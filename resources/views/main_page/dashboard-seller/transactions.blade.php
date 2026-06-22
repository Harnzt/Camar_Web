@extends('main_page.layout.app')

@section('title', 'Riwayat Transaksi Seller - CAMAR')
@section('description', 'Pantau semua transaksi masuk untuk kredit karbon Anda di CAMAR')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard-seller.css') }}">
    <link rel="stylesheet" href="{{ asset('css/transactions.css') }}">
@endpush

@section('content')
<div class="seller-wrapper">

    {{-- =====================================================
         TOPBAR KONSISTEN SELLER
         ===================================================== --}}
    <div class="seller-topbar">
        <div class="seller-topbar-inner">
            <div class="topbar-left">
                <a href="{{ route('seller.dashboard') }}" class="btn-back-dashboard" title="Kembali ke Dashboard">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="seller-identity">
                    <span class="seller-greeting">Kelola Penjualan</span>
                    <h1 class="seller-name">Riwayat Transaksi</h1>
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn-export" id="btnExportCSV">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7,10 12,15 17,10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>

    <div class="seller-body">
        
        {{-- =====================================================
             SUMMARY STATS CARDS (Gabungan Code 1 & Code 2)
             ===================================================== --}}
        <div class="tx-summary">
            <div class="summary-card">
                <span class="summary-label">Total Transaksi</span>
                <span class="summary-value" id="statTotal">{{ number_format($stats['total_count'] ?? 0) }}</span>
            </div>
            <div class="summary-card success">
                <span class="summary-label">Berhasil / Dibayar</span>
                <span class="summary-value" id="statSuccess">{{ number_format($stats['paid_count'] ?? 0) }}</span>
            </div>
            <div class="summary-card warning">
                <span class="summary-label">Menunggu</span>
                <span class="summary-value" id="statPending">{{ number_format($stats['pending_count'] ?? 0) }}</span>
            </div>
            <div class="summary-card danger">
                <span class="summary-label">Gagal</span>
                <span class="summary-value" id="statFailed">{{ number_format($stats['failed_count'] ?? 0) }}</span>
            </div>
            <div class="summary-card info">
                <span class="summary-label">Total Pendapatan</span>
                <span class="summary-value" id="statAmount">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- =====================================================
             FILTER & TOOLBAR (Pencarian, Status, & Rentang Tanggal)
             ===================================================== --}}
        <div class="tx-toolbar">
            <form action="{{ route('transactions.index') }}" method="GET" class="filter-form-wrapper" style="display: flex; width: 100%; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                
                <div class="tx-search" style="flex: 1; min-width: 250px;">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" id="searchInput" class="search-input" placeholder="Cari ID, nama pembeli, atau proyek..." value="{{ request('search') }}">
                </div>

                <div class="tx-filters" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                    {{-- Filter Status --}}
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select name="status" id="filterStatus" class="filter-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                        </select>
                    </div>

                    {{-- Filter Tanggal Mulai --}}
                    <div class="filter-group">
                        <label class="filter-label">Dari</label>
                        <input type="date" name="date_from" id="filterDateFrom" class="filter-input" value="{{ request('date_from') }}">
                    </div>

                    {{-- Filter Tanggal Selesai --}}
                    <div class="filter-group">
                        <label class="filter-label">Sampai</label>
                        <input type="date" name="date_to" id="filterDateTo" class="filter-input" value="{{ request('date_to') }}">
                    </div>

                    {{-- Tombol Aksi Filter --}}
                    <button type="submit" class="btn-filter-submit" style="padding: 0.75rem 1.25rem; background: var(--color-teal, #26667F); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    @if(request()->has('search') || request()->has('status') || request()->has('date_from') || request()->has('date_to'))
                        <a href="{{ route('transactions.index') }}" class="btn-reset" id="btnReset" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 40px; padding: 0 1rem; border: 1px solid #cbd5e1; border-radius: 8px; color: #64748b; background: white;">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- =====================================================
             STATUS INTERACTIVE TABS
             ===================================================== --}}
        <div class="tx-tabs">
            <a href="{{ route('transactions.index') }}" class="tab-btn {{ !request('status') ? 'active' : '' }}">Semua</a>
            <a href="{{ route('transactions.index', array_merge(request()->query(), ['status' => 'paid'])) }}" class="tab-btn {{ request('status') == 'paid' ? 'active' : '' }}">
                Berhasil <span class="tab-badge success">{{ $stats['paid_count'] ?? 0 }}</span>
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->query(), ['status' => 'pending'])) }}" class="tab-btn {{ request('status') == 'pending' ? 'active' : '' }}">
                Menunggu <span class="tab-badge warning">{{ $stats['pending_count'] ?? 0 }}</span>
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->query(), ['status' => 'failed'])) }}" class="tab-btn {{ request('status') == 'failed' ? 'active' : '' }}">
                Gagal <span class="tab-badge danger">{{ $stats['failed_count'] ?? 0 }}</span>
            </a>
        </div>

        {{-- =====================================================
             TABLE DATA TRANSAKSI
             ===================================================== --}}
        <div class="tx-table-wrapper dashboard-card card-table" style="margin-top: 1rem; position: relative;">
            <div id="loadingOverlay" class="loading-overlay hidden">
                <div class="spinner"></div>
            </div>

            <table class="tx-table" id="txTable">
                <thead>
                    <tr>
                        <th class="sortable" data-col="id">ID Order</th>
                        <th class="sortable" data-col="created_at">Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Proyek Carbon</th>
                        <th>Jumlah (Qty)</th>
                        <th class="sortable" data-col="total_price">Total Pendapatan</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="txBody">
                    @forelse($orders as $tx)
                    <tr class="tx-row" data-id="{{ $tx->id }}">
                        <td>
                            <span class="tx-id">#{{ $tx->id }}</span>
                        </td>
                        <td>
                            <span class="tx-date">{{ $tx->created_at->format('d M Y') }}</span>
                            <span class="tx-time" style="display: block; font-size: 0.75rem; color: #94a3b8;">{{ $tx->created_at->format('H:i') }}</span>
                        </td>
                        <td>
                            <div class="tx-customer" style="display: flex; align-items: center; gap: 0.5rem;">
                                <div class="avatar" style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; color: #475569;">
                                    {{ strtoupper(substr($tx->user->name ?? 'B', 0, 1)) }}
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span class="customer-name" style="font-weight: 600; color: #1e293b;">{{ $tx->user->name ?? 'Buyer Luar' }}</span>
                                    <span class="customer-email" style="font-size: 0.75rem; color: #64748b;">{{ $tx->user->email ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="project-name-link" style="font-weight: 500;">{{ $tx->project->name ?? 'Proyek Terhapus' }}</span>
                        </td>
                        <td>
                            <strong>{{ number_format($tx->quantity, 0, ',', '.') }}</strong> <span class="unit-label" style="font-size: 0.75rem; color: #64748b;">ton CO₂</span>
                        </td>
                        <td>
                            <span class="tx-amount" style="font-weight: 700; color: var(--color-navy, #124170);">Rp {{ number_format($tx->total_price, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $tx->status }} {{ $tx->status }}">
                                {{ ucfirst($tx->status) }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button class="btn-icon btn-view btn-detail-trx" data-id="{{ $tx->id }}" data-detail="{{ json_encode($tx->load(['project', 'user'])) }}" title="Lihat Detail" style="background: none; border: none; color: var(--color-green, #67C090); cursor: pointer; padding: 4px 8px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="tx-empty" style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; color: #cbd5e1;">
                                    <path d="M9 17H7A5 5 0 0 1 7 7h2"/><path d="M15 7h2a5 5 0 1 1 0 10h-2"/><line x1="8" y1="12" x2="16" y2="12"/>
                                </svg>
                                <p style="font-weight: 600; margin-bottom: 0.25rem;">Tidak ditemukan riwayat transaksi yang cocok.</p>
                                <span style="font-size: 0.85rem; color: #94a3b8;">Transaksi penjualan masuk Anda akan muncul di sini.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- =====================================================
             PAGINATION COMPONENT
             ===================================================== --}}
        @if($orders->hasPages())
        <div class="tx-pagination" style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <span class="pagination-info" style="font-size: 0.875rem; color: #64748b;">
                Menampilkan {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }}
                dari {{ $orders->total() }} transaksi
            </span>
            <div class="pagination-links">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
        @endif

    </div>{{-- /seller-body --}}
</div>{{-- /seller-wrapper --}}

{{-- =====================================================
     MODAL DETAIL TRANSAKSI (POP-UP)
     ===================================================== --}}
<div class="modal-backdrop hidden" id="modalBackdrop">
    <div class="modal" id="txModal" style="background: #fff; border-radius: 12px; max-width: 550px; width: 90%; margin: 10% auto; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h2 class="modal-title" style="font-size: 1.25rem; font-weight: 700; color: #1e293b;"><i class="fas fa-receipt"></i> Rincian Transaksi Masuk</h2>
            <button class="modal-close" id="modalClose" style="background: none; border: none; color: #94a3b8; cursor: pointer;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" id="modalBody" style="padding-top: 1.25rem;">
            <div class="modal-loading">
                <div class="spinner"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.transactionRoutes = {
            index: "{{ route('transactions.index') }}",
        };
    </script>
    <script src="{{ asset('js/transactions.js') }}"></script>
@endpush