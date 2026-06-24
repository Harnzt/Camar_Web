@extends('main_page.layout.app')

@section('title', 'Dashboard - CAMAR')
@section('description', 'Dashboard Buyer CAMAR - Pantau emisi karbon dan progress offset Anda')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-buyer.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
@endpush

@section('content')

<div class="dashboard-wrapper">

    {{-- =====================================================
         TOPBAR
         ===================================================== --}}
    <div class="dashboard-topbar">
        <div class="topbar-inner">
            <div class="topbar-greeting">
                <div class="greeting-avatar">
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                </div>
                <div class="greeting-text">
                    <span class="greeting-label">Selamat datang kembali,</span>
                    <h1 class="greeting-name">{{ Auth::user()->name }}</h1>
                    <div class="greeting-meta">
                        <span class="badge-role">{{ Auth::user()->role_label }}</span>
                        <span class="badge-category">{{ Auth::user()->category_label }}</span>
                        @if(Auth::user()->isPending())
                            <span class="badge-status pending">
                                <i class="fas fa-clock"></i> Menunggu Verifikasi
                            </span>
                        @elseif(Auth::user()->isVerified())
                            <span class="badge-status verified">
                                <i class="fas fa-check-circle"></i> Terverifikasi
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="topbar-actions">
                <a href="{{ route('calculator') }}" class="btn-topbar primary">
                    <i class="fas fa-calculator"></i> Hitung Emisi
                </a>
                <a href="{{ route('dashboard') }}" class="btn-topbar secondary">
                    <i class="fas fa-leaf"></i> Offset Sekarang
                </a>
            </div>
        </div>
    </div>

    {{-- =====================================================
         PENDING BANNER
         ===================================================== --}}
    @if(Auth::user()->isPending())
    <div class="pending-banner">
        <div class="pending-banner-inner">
            <div class="pending-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="pending-text">
                <strong>Akun Anda sedang dalam proses verifikasi.</strong>
                <span>Dokumen yang Anda upload sedang diperiksa oleh tim CAMAR. Proses ini membutuhkan 1–2 hari kerja.</span>
            </div>
        </div>
    </div>
    @endif

    <div class="dashboard-body">

        {{-- =====================================================
             STAT CARDS
             ===================================================== --}}
        <div class="stat-cards">

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="fas fa-smog"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Total Emisi</span>
                    <div class="stat-value">
                        {{ number_format($emission->total_kg ?? 0, 1) }}
                        <span class="stat-unit">kg CO₂</span>
                    </div>
                    <span class="stat-sub">≈ {{ number_format($emission->total_ton ?? 0, 3) }} ton</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #67C090, #26667F);">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Sudah Ter-offset</span>
                    <div class="stat-value">
                        {{ number_format($totalOffsetKg ?? 0, 1) }}
                        <span class="stat-unit">kg CO₂</span>
                    </div>
                    <span class="stat-sub">dari {{ number_format($emission->total_kg ?? 0, 1) }} kg total</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Estimasi Biaya Offset</span>
                    <div class="stat-value" style="font-size:1.3rem;">
                        Rp {{ number_format($emission->estimated_cost ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="stat-sub">berdasarkan kalkulasi terakhir</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #124170, #26667F);">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Total Transaksi</span>
                    <div class="stat-value">
                        {{ $totalTransactions ?? 0 }}
                        <span class="stat-unit">transaksi</span>
                    </div>
                    <span class="stat-sub">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }} dibelanjakan</span>
                </div>
            </div>

        </div>

        {{-- =====================================================
             ROW 2 — Grafik Offset + Rincian Scope
             ===================================================== --}}
        <div class="dashboard-row row-two">

            {{-- Donut Chart --}}
            <div class="panel panel-offset">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-chart-pie"></i> Status Carbon Offset
                    </h2>
                    <a href="{{ route('calculator') }}" class="panel-link">Hitung ulang →</a>
                </div>
                <div class="panel-body">
                    <div class="offset-chart-wrap">
                        <div class="offset-donut">
                            <canvas id="offsetChart"></canvas>
                            <div class="donut-center">
                                <span class="donut-pct">{{ round($offsetPercentage ?? 0, 1) }}%</span>
                                <span class="donut-label">Ter-offset</span>
                            </div>
                        </div>
                        <div class="offset-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background:#67C090;"></span>
                                <div>
                                    <div class="legend-val">{{ number_format($totalOffsetKg ?? 0, 1) }} kg</div>
                                    <div class="legend-lbl">Sudah Ter-offset</div>
                                </div>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background:#e2e8f0;"></span>
                                <div>
                                    <div class="legend-val">{{ number_format(max(0, ($emission->total_kg ?? 0) - ($totalOffsetKg ?? 0)), 1) }} kg</div>
                                    <div class="legend-lbl">Sisa Emisi</div>
                                </div>
                            </div>
                            <div class="legend-divider"></div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background:#124170;"></span>
                                <div>
                                    <div class="legend-val">{{ $treeEquivalent ?? 0 }} pohon</div>
                                    <div class="legend-lbl">Setara pohon ditanam</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="offset-progress-wrap">
                        <div class="offset-progress-header">
                            <span>Progress Offset</span>
                            <strong>{{ round($offsetPercentage ?? 0, 1) }}%</strong>
                        </div>
                        <div class="offset-progress-track">
                            <div class="offset-progress-fill" style="width: {{ min(100, $offsetPercentage ?? 0) }}%"></div>
                        </div>
                        <div class="offset-progress-footer">
                            @if(($offsetPercentage ?? 0) >= 100)
                                <span class="progress-msg success"><i class="fas fa-trophy"></i> Emisi Anda 100% ter-offset!</span>
                            @elseif(($offsetPercentage ?? 0) > 50)
                                <span class="progress-msg good"><i class="fas fa-thumbs-up"></i> Lebih dari setengah emisi sudah ter-offset</span>
                            @elseif(($offsetPercentage ?? 0) > 0)
                                <span class="progress-msg warn"><i class="fas fa-bolt"></i> Terus tingkatkan offset Anda</span>
                            @else
                                <span class="progress-msg neutral"><i class="fas fa-info-circle"></i> Mulai offset emisi Anda sekarang</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rincian per Scope --}}
            <div class="panel panel-scope">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-layer-group"></i> Rincian Emisi per Scope
                    </h2>
                    <div class="scope-header-actions">
                        <span class="panel-date">
                            {{ $emission ? $emission->created_at->format('d M Y') : 'Belum ada data' }}
                        </span>
                        @if($emission && !empty($emission->scope_details))
                            <button type="button" class="scope-detail-button" data-open-scope-detail>
                                <i class="fas fa-list-ul"></i> Lihat Detail
                            </button>
                        @endif
                    </div>
                </div>
                <div class="panel-body">
                    @if($emission)
                        @php
                            $s1   = $emission->scope1_kg ?? 0; 
                            $s2   = $emission->scope2_kg ?? 0; 
                            $s3   = $emission->scope3_kg ?? 0; 
                            $tot  = $emission->total_kg  ?? 0;
                            $scopeDetails = $emission->scope_details ?? [];
                            
                            $pct1 = $tot > 0 ? ($s1 / $tot * 100) : 0;
                            $pct2 = $tot > 0 ? ($s2 / $tot * 100) : 0;
                            $pct3 = $tot > 0 ? ($s3 / $tot * 100) : 0;
                        @endphp

                        <div class="scope-item">
                            <div class="scope-header">
                                <div class="scope-badge s1">S1</div>
                                <div class="scope-info">
                                    <span class="scope-name">Emisi Langsung</span>
                                    <span class="scope-desc">Bahan bakar & pembakaran</span>
                                </div>
                                <div class="scope-value">{{ number_format($s1, 1) }} kg</div>
                            </div>
                            <div class="scope-bar-track">
                                <div class="scope-bar-fill s1" style="width:{{ $pct1 }}%"></div>
                            </div>
                            <span class="scope-pct">{{ round($pct1, 1) }}%</span>
                        </div>

                        <div class="scope-item">
                            <div class="scope-header">
                                <div class="scope-badge s2">S2</div>
                                <div class="scope-info">
                                    <span class="scope-name">Energi Tidak Langsung</span>
                                    <span class="scope-desc">Konsumsi listrik</span>
                                </div>
                                <div class="scope-value">{{ number_format($s2, 1) }} kg</div>
                            </div>
                            <div class="scope-bar-track">
                                <div class="scope-bar-fill s2" style="width:{{ $pct2 }}%"></div>
                            </div>
                            <span class="scope-pct">{{ round($pct2, 1) }}%</span>
                        </div>

                        <div class="scope-item">
                            <div class="scope-header">
                                <div class="scope-badge s3">S3</div>
                                <div class="scope-info">
                                    <span class="scope-name">Emisi Lainnya</span>
                                    <span class="scope-desc">Transportasi & limbah</span>
                                </div>
                                <div class="scope-value">{{ number_format($s3, 1) }} kg</div>
                            </div>
                            <div class="scope-bar-track">
                                <div class="scope-bar-fill s3" style="width:{{ $pct3 }}%"></div>
                            </div>
                            <span class="scope-pct">{{ round($pct3, 1) }}%</span>
                        </div>

                        <div class="scope-total">
                            <span>Total Emisi</span>
                            <strong>{{ number_format($tot, 1) }} kg CO₂</strong>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-calculator"></i></div>
                            <p>Belum ada data kalkulasi emisi</p>
                            <a href="{{ route('calculator') }}" class="btn-empty">Mulai Kalkulasi</a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- =====================================================
             ROW 3 — Riwayat Transaksi + Sertifikat
             ===================================================== --}}
        @if($emission && !empty($scopeDetails))
            <dialog class="scope-detail-modal" id="scope-detail-modal">
                <div class="scope-detail-modal-card">
                    <div class="scope-detail-modal-header">
                        <div>
                            <span class="scope-modal-kicker">Kalkulasi {{ $emission->created_at->format('d M Y') }}</span>
                            <h3>Detail Rincian Emisi</h3>
                        </div>
                        <button type="button" class="scope-modal-close" data-close-scope-detail aria-label="Tutup">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    <div class="scope-detail-modal-body">
                        @foreach([
                            ['key' => 'scope1', 'badge' => 'S1', 'class' => 's1', 'title' => 'Emisi Langsung', 'total' => $s1],
                            ['key' => 'scope2', 'badge' => 'S2', 'class' => 's2', 'title' => 'Energi Tidak Langsung', 'total' => $s2],
                            ['key' => 'scope3', 'badge' => 'S3', 'class' => 's3', 'title' => 'Emisi Lainnya', 'total' => $s3],
                        ] as $scope)
                            <section class="scope-modal-section">
                                <div class="scope-modal-section-header">
                                    <div class="scope-badge {{ $scope['class'] }}">{{ $scope['badge'] }}</div>
                                    <div>
                                        <strong>{{ $scope['title'] }}</strong>
                                        <span>{{ number_format($scope['total'], 1) }} kg CO₂</span>
                                    </div>
                                </div>
                                <div class="scope-modal-rows">
                                    @forelse($scopeDetails[$scope['key']] ?? [] as $detail)
                                        <div class="scope-modal-row">
                                            <span>{{ $detail['label'] }}</span>
                                            <strong>{{ number_format((float) $detail['value_kg'], 1) }} kg CO₂</strong>
                                        </div>
                                    @empty
                                        <div class="scope-modal-row empty">
                                            <span>Rincian komponen belum tersedia.</span>
                                        </div>
                                    @endforelse
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="scope-detail-modal-footer">
                        <div>
                            <span>Total Emisi</span>
                            <strong>{{ number_format($tot, 1) }} kg CO₂</strong>
                        </div>
                        <button type="button" class="scope-modal-done" data-close-scope-detail>Tutup</button>
                    </div>
                </div>
            </dialog>
        @endif

        <div class="dashboard-row row-three">

            <div class="panel panel-transactions">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-history"></i> Riwayat Transaksi
                    </h2>
                    @if($transactions->count() > 0)
                        <a href="{{ route('buyer.transactions') }}" class="panel-link">Lihat semua →</a>
                    @endif
                </div>
                <div class="panel-body p-0">
                    @if($transactions->count() > 0)
                        <div class="trx-list">
                            @foreach($transactions as $trx)
                            <div class="trx-item">
                                <div class="trx-icon"><i class="fas fa-leaf"></i></div>
                                <div class="trx-info">
                                    <span class="trx-name">{{ $trx->project->name ?? '-' }}</span>
                                    <span class="trx-code">{{ $trx->transaction_code }}</span>
                                    <span class="trx-date">{{ $trx->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="trx-right">
                                    <span class="trx-amount">{{ $trx->formatted_total }}</span>
                                    <span class="trx-offset">-{{ $trx->offset_ton }} ton CO₂</span>
                                    <span class="trx-status" style="color:{{ $trx->status_color }};">
                                        {{ $trx->status_label }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-receipt"></i></div>
                            <p>Belum ada transaksi</p>
                            <a href="{{ route('dashboard') }}" class="btn-empty">Mulai Offset</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="panel panel-certificates">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-certificate"></i> Sertifikat Digital
                    </h2>
                </div>
                <div class="panel-body">
                    @php $completedTrx = $transactions->where('status', 'completed'); @endphp
                    @if($completedTrx->count() > 0)
                        <div class="cert-list">
                            @foreach($completedTrx as $trx)
                            <div class="cert-card">
                                <div class="cert-ribbon"></div>
                                <div class="cert-logo">
                                    <img src="{{ asset('images/logo-camar.svg') }}" alt="CAMAR">
                                </div>
                                <div class="cert-body">
                                    <span class="cert-title">Carbon Offset Certificate</span>
                                    <span class="cert-name">{{ Auth::user()->name }}</span>
                                    <span class="cert-detail">{{ $trx->offset_ton }} ton CO₂ · {{ $trx->project->name ?? '-' }}</span>
                                    <span class="cert-number">{{ $trx->certificate_number }}</span>
                                </div>
                                <button class="cert-download" onclick="downloadCertificate('{{ $trx->id }}')">
                                    <i class="fas fa-download"></i> Unduh
                                </button>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="cert-placeholder">
                                <div class="cert-placeholder-inner">
                                    <i class="fas fa-certificate"></i>
                                    <span>Sertifikat CAMAR</span>
                                    <small>Carbon Offset</small>
                                </div>
                            </div>
                            <p>Sertifikat diterbitkan setelah transaksi selesai diverifikasi</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- =====================================================
             ROW 4 — Rekomendasi Proyek
             ===================================================== --}}
        <div class="panel panel-projects">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i class="fas fa-seedling"></i> Rekomendasi Proyek Carbon Offset
                </h2>
                <!-- <a href="{{ route('buyer.transactions') }}" class="panel-link">Lihat semua →</a> -->
            </div>
            <div class="panel-body">
                @if($recommendedProjects->count() > 0)
                    <div class="project-grid">
                        @foreach($recommendedProjects as $project)
                        <div class="project-card">
                            <div class="project-badge {{ !$emission ? 'project-badge--initial' : '' }}" style="background: #26667F;">
                                @if($emission)
                                    Cocok {{ number_format($project->recommendation_score, 0) }}%
                                @else
                                    {{ $project->recommendation_label }}
                                @endif
                            </div>
                            <div class="project-image">
                                <img src="{{ $project->image_url }}" alt="{{ $project->name }}">
                                <div class="project-type-icon">
                                    @switch($project->category)
                                        @case('mangrove')  @break
                                        @case('solar')     @break
                                        @case('forest')    @break
                                        @case('wind')      @break
                                        @default          
                                    @endswitch
                                </div>
                            </div>
                            <div class="project-body">
                                <h3 class="project-name">{{ $project->name }}</h3>
                                <p class="project-company">{{ $project->company_name }}</p>
                                <p class="project-desc">{{ Str::limit($project->description, 80) }}</p>
                                @if($emission)
                                    <div class="project-match">
                                        <div class="project-match-head">
                                            <span>Skor rekomendasi</span>
                                            <strong>{{ number_format($project->recommendation_score, 1) }}/100</strong>
                                        </div>
                                        <div class="project-match-track">
                                            <div class="project-match-fill"
                                                style="width: {{ min(100, $project->recommendation_score) }}%">
                                            </div>
                                        </div>
                                        <p>{{ $project->recommendation_reasons[0] ?? 'Proyek aktif dengan stok tersedia.' }}</p>
                                    </div>
                                @endif
                                <div class="project-stats">
                                    <div class="project-stat">
                                        <i class="fas fa-cloud"></i>
                                        <span>{{ $project->co2_per_year }} ton CO₂/tahun</span>
                                    </div>
                                    <div class="project-stat">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $project->duration_months }} bulan</span>
                                    </div>
                                    <div class="project-stat">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>{{ $project->location }}</span>
                                    </div>
                                    @if($project->area_ha)
                                    <div class="project-stat">
                                        <i class="fas fa-expand-alt"></i>
                                        <span>{{ number_format($project->area_ha) }} ha</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="project-footer">
                                    <div class="project-price">
                                        {{ $project->price_formatted }}
                                        <span>/ton CO₂</span>
                                    </div>
                                    <a href="{{ route('projects.show', $project->id) }}" class="btn-project">Pilih Paket</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state horizontal">
                        <div class="empty-icon"><i class="fas fa-seedling"></i></div>
                        <div>
                            <p>Belum ada proyek yang tersedia</p>
                            <small>Proyek akan muncul di sini setelah seller menambahkan paket</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- =====================================================
             QUICK ACTIONS
             ===================================================== --}}
        <div class="quick-actions">
            <a href="{{ route('calculator') }}" class="quick-action-card">
                <div class="qa-icon" style="background: linear-gradient(135deg, #124170, #26667F);">
                    <i class="fas fa-calculator"></i>
                </div>
                <span class="qa-label">Kalkulator Emisi</span>
                <span class="qa-desc">Hitung emisi baru</span>
            </a>
            <a href="{{ route('projects.index') }}" class="quick-action-card">
                <div class="qa-icon" style="background: linear-gradient(135deg, #67C090, #26667F);">
                    <i class="fas fa-leaf"></i>
                </div>
                <span class="qa-label">Browse Proyek</span>
                <span class="qa-desc">Temukan proyek offset</span>
            </a>
            <a href="{{ route('buyer.transactions') }}" class="quick-action-card">
                <div class="qa-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <span class="qa-label">Riwayat Lengkap</span>
                <span class="qa-desc">Semua transaksi</span>
            </a>
            <a href="#" class="quick-action-card">
                <div class="qa-icon" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                    <i class="fas fa-user-cog"></i>
                </div>
                <span class="qa-label">Edit Profil</span>
                <span class="qa-desc">Kelola akun Anda</span>
            </a>
        </div>

    </div>{{-- /dashboard-body --}}
</div>{{-- /dashboard-wrapper --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const DASHBOARD_DATA = {
        offsetPercentage : {{ round($offsetPercentage ?? 0, 1) }},
        totalOffsetKg    : {{ $totalOffsetKg ?? 0 }},
        remainingKg      : {{ max(0, ($emission->total_kg ?? 0) - ($totalOffsetKg ?? 0)) }},
        scope1           : {{ $emission->scope1_kg ?? 0 }},
        scope2           : {{ $emission->scope2_kg ?? 0 }},
        scope3           : {{ $emission->scope3_kg ?? 0 }},
    };
</script>
<script src="{{ asset('js/dashboard-buyer.js') }}"></script>
@endpush
