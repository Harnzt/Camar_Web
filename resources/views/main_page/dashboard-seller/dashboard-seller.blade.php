@extends('main_page.layout.app')

@section('title', 'Dashboard Seller - CAMAR')
@section('description', 'Kelola proyek dan pantau penjualan kredit karbon Anda di CAMAR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-seller.css') }}">
@endpush

@section('content')

<div class="seller-wrapper">

    {{-- =====================================================
         TOPBAR
         ===================================================== --}}
    <div class="seller-topbar">
        <div class="seller-topbar-inner">

            <div class="topbar-left">
                <div class="seller-avatar">
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                    <span class="seller-online-dot"></span>
                </div>
                <div class="seller-identity">
                    <span class="seller-greeting">Dashboard Seller</span>
                    <h1 class="seller-name">{{ Auth::user()->name }}</h1>
                    <div class="seller-meta">
                        @if(Auth::user()->company_name)
                            <span class="meta-chip company">
                                <i class="fas fa-building"></i>
                                {{ Auth::user()->company_name }}
                            </span>
                        @endif
                        <span class="meta-chip role">Seller</span>
                        @if(Auth::user()->isVerified())
                            <span class="meta-chip verified">
                                <i class="fas fa-shield-alt"></i> Terverifikasi
                            </span>
                        @else
                            <span class="meta-chip pending">
                                <i class="fas fa-clock"></i> Menunggu Verifikasi
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="topbar-right">
                {{-- Alert Pending Pembayaran --}}
                @if($pendingCount > 0)
                <div class="pending-alert">
                    <i class="fas fa-bell"></i>
                    <div>
                        <strong>{{ $pendingCount }} transaksi menunggu</strong>
                        <span>Rp {{ number_format($pendingAmount, 0, ',', '.') }} belum dikonfirmasi</span>
                    </div>
                </div>
                @endif

                <!-- <a href="#upload-proyek" class="btn-add-project"> -->
                <a href="{{ route('seller.projects.create') }}" class="btn-add-project">
                    <i class="fas fa-plus"></i>
                    Tambah Proyek
                </a>
            </div>

        </div>
    </div>

    <div class="seller-body">

        {{-- =====================================================
             STAT CARDS
             ===================================================== --}}
        <div class="stat-cards">

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Total Pendapatan</span>
                    <div class="stat-value">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </div>
                    <span class="stat-sub">dari transaksi terkonfirmasi</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #67C090, #26667F);">
                    <i class="fas fa-cloud"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Total Carbon Sold</span>
                    <div class="stat-value">
                        {{ number_format((float)$totalCarbonSold, ($totalCarbonSold == round($totalCarbonSold) ? 0 : 2), ',', '.') }}
                        <span class="stat-unit">ton CO₂</span>
                    </div>
                    <span class="stat-sub">berhasil di-offset buyer</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #124170, #26667F);">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Proyek Aktif</span>
                    <div class="stat-value">
                        {{ $activeProjects }}
                        <span class="stat-unit">proyek</span>
                    </div>
                    <span class="stat-sub">tersedia di marketplace</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-card-body">
                    <span class="stat-label">Sisa Stok</span>
                    <div class="stat-value">
                        {{ number_format((float)$totalStock, ($totalStock == round($totalStock) ? 0 : 2), ',', '.') }}
                        <span class="stat-unit">ton</span>
                    </div>
                    <span class="stat-sub">total stok tersedia</span>
                </div>
            </div>

        </div>

        {{-- =====================================================
             ROW 2 — Sales Chart + Recent Sales
             ===================================================== --}}
        <div class="seller-row row-main">

            {{-- Grafik Penjualan --}}
            <div class="panel panel-chart">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-chart-line"></i>
                        Tren Penjualan Bulanan
                    </h2>
                    <div class="chart-legend">
                        <span class="legend-dot-inline" style="background:#67C090;"></span>
                        <span>Pendapatan</span>
                        <span class="legend-dot-inline" style="background:#124170;"></span>
                        <span>Carbon Sold</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="chart-wrap">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Recent Sales --}}
            <div class="panel panel-recent-sales">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-shopping-bag"></i>
                        Transaksi Masuk
                    </h2>
                    <a href="{{ route('transactions.index') }}" class="panel-link">Semua →</a>
                </div>
                <div class="panel-body p-0">
                    @if($recentSales->count() > 0)
                        <div class="sales-list">
                            @foreach($recentSales as $trx)
                            <div class="sale-item">
                                <div class="sale-buyer-avatar">
                                    {{ strtoupper(substr($trx->buyer->name ?? 'B', 0, 1)) }}
                                </div>
                                <div class="sale-info">
                                    <span class="sale-buyer">{{ $trx->buyer->name ?? '-' }}</span>
                                    <span class="sale-project">{{ Str::limit($trx->project->name ?? '-', 28) }}</span>
                                    <span class="sale-date">{{ $trx->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="sale-right">
                                    <span class="sale-amount">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</span>
                                    <span class="sale-carbon">{{ $trx->offset_ton }} ton</span>
                                    <span class="sale-status-dot" style="background:{{ $trx->status_color }};"
                                          title="{{ $trx->status_label }}"></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-shopping-bag"></i></div>
                            <p>Belum ada transaksi</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- =====================================================
             ROW 3 — Manajemen Proyek
             ===================================================== --}}
        <div class="panel panel-projects">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i class="fas fa-layer-group"></i>
                    Manajemen Proyek Anda
                </h2>
                <!-- <a href="#upload-proyek" class="btn-add-small"> -->
                <a href="{{ route('seller.projects.create') }}" class="btn-add-small">
                    <i class="fas fa-plus"></i> Proyek Baru
                </a>
            </div>
            <div class="panel-body p-0">
                @if($projects->count() > 0)
                <div class="projects-table-wrap">
                    <table class="projects-table">
                        <thead>
                            <tr>
                                <th>Proyek</th>
                                <th>Standar</th>
                                <th>Stok (ton)</th>
                                <th>Harga/ton</th>
                                <th>Terjual</th>
                                <th>Pendapatan</th>
                                <th>Impact</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr class="project-row">
                                <td>
                                    <div class="project-cell">
                                        <div class="project-thumb">
                                            <img src="{{ $project->image_url }}" alt="{{ $project->name }}">
                                        </div>
                                        <div class="project-cell-info">
                                            <span class="project-cell-name">{{ $project->name }}</span>
                                            <span class="status-badge status-{{ $project->verification_status }}">
                                                {{ match($project->verification_status) {
                                                    'approved' => 'Disetujui',
                                                    'rejected' => 'Ditolak',
                                                    'revision_required' => 'Perlu Revisi',
                                                    default => 'Menunggu Verifikasi',
                                                } }}
                                            </span>
                                            @if(in_array($project->verification_status, ['rejected', 'revision_required']) && $project->admin_notes)
                                                <span class="project-review-note">
                                                    <i class="fas fa-circle-info"></i> {{ $project->admin_notes }}
                                                </span>
                                            @endif
                                            <span class="project-cell-loc">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $project->location }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="standard-badge">{{ $project->standard ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="stock-cell">
                                        <span class="stock-val">
                                            {{ number_format((float)$project->stock_available, ($project->stock_available == round($project->stock_available) ? 0 : 1), ',', '.') }}
                                        </span>
                                        @if($project->stock_available < 10)
                                            <span class="stock-warn" title="Stok hampir habis">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="price-cell">
                                        Rp {{ number_format($project->price_per_ton, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="sold-cell">
                                        <!-- {{ number_format($project->carbon_sum ?? 0, 2) }} ton -->
                                        {{ number_format((float)($project->carbon_sum ?? 0), ($project->carbon_sum == round($project->carbon_sum ?? 0) ? 0 : 2), ',', '.') }} ton
                                    </span>
                                </td>
                                <td>
                                    <span class="revenue-cell">
                                        Rp {{ number_format($project->revenue_sum ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="impact-cell">
                                        @if($project->families_impacted)
                                            <span class="impact-item">
                                                <i class="fas fa-users"></i>
                                                {{ number_format($project->families_impacted) }} KK
                                            </span>
                                        @endif
                                        @if($project->area_ha)
                                            <span class="impact-item">
                                                <i class="fas fa-map"></i>
                                                {{ number_format($project->area_ha) }} ha
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($project->stock_available > 0)
                                        <span class="status-pill active">Aktif</span>
                                    @else
                                        <span class="status-pill sold-out">Habis</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn edit" title="Edit Proyek"
                                            onclick="editProject({{ $project->id }})">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <form action="{{ route('seller.projects.destroy', $project->id) }}" method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini secara permanen dari pasar CAMAR?')" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete" title="Hapus Proyek" 
                                                    style="background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; transition: background 0.2s;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="empty-state" style="padding: 3rem;">
                        <div class="empty-icon"><i class="fas fa-seedling"></i></div>
                        <p>Belum ada proyek yang ditambahkan</p>
                        <a href="#upload-proyek" class="btn-empty">+ Tambah Proyek Pertama</a>
                    </div>
                @endif
            </div>
        </div>


    </div>{{-- /seller-body --}}
</div>{{-- /seller-wrapper --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const SELLER_DATA = {
        chartLabels  : {!! json_encode($chartLabels) !!},
        chartRevenue : {!! json_encode($chartRevenue) !!},
        chartCarbon  : {!! json_encode($chartCarbon) !!},
    };
</script>
<script src="{{ asset('js/dashboard-seller.js') }}"></script>
@endpush
