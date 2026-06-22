@extends('main_page.layout.app')

@section('title', 'Riwayat Transaksi Buyer - CAMAR')
@section('description', 'Pantau status transaksi dan masa aktif proyek carbon offset Anda')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-buyer.css') }}">
<link rel="stylesheet" href="{{ asset('css/buyer-transactions.css') }}">
@endpush

@section('content')
<div class="dashboard-wrapper">

    {{-- TOPBAR KONSISTEN --}}
    <div class="dashboard-topbar">
        <div class="topbar-inner">
            <div class="topbar-greeting">
                <a href="{{ route('dashboard') }}" class="btn-back-dashboard" title="Kembali ke Dashboard">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="greeting-text">
                    <span class="greeting-label">Aktivitas Akun Anda</span>
                    <h1 class="greeting-name">Riwayat Transaksi & Proyek</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-body">

        {{-- CARD RINGKASAN DATA INVESTASI BUYER --}}
        <div class="stat-cards" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(18, 65, 112, 0.1); color: var(--color-navy);"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Total Transaksi</span>
                    <h3 class="stat-value">{{ $stats['total_transactions'] }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(103, 192, 144, 0.1); color: var(--color-green);"><i class="fas fa-leaf"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Kredit Karbon Ter-offset</span>
                    <h3 class="stat-value">{{ number_format($stats['total_offset_ton'], 2, ',', '.') }} <span style="font-size: 0.9rem;">ton</span></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(38, 102, 127, 0.1); color: var(--color-teal);"><i class="fas fa-wallet"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Total Pengeluaran</span>
                    <h3 class="stat-value" style="font-size: 1.35rem;">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(38, 102, 127, 0.1); color: var(--color-teal);"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Status Proyek Anda</span>
                    <div style="margin-top: 5px; display: flex; flex-direction: column; gap: 4px; font-size: 0.85rem;">
                        <span style="color: #0369a1; font-weight: 600;">Berjalan: <strong>{{ $stats['proj_running'] }}</strong></span>
                        <span style="color: #e11d48; font-weight: 600;">Expired &lt;3 Hari: <strong style="background: #ffe4e6; padding: 1px 5px; border-radius: 4px;">{{ $stats['proj_expiring'] }}</strong></span>
                        <span style="color: #64748b; font-weight: 600;">Selesai: <strong>{{ $stats['proj_completed'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAR FILTER PENCARIAN & STATUS --}}
        <div class="panel" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <form action="{{ route('buyer.transactions') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%; align-items: center;">
                <div style="flex: 1; min-width: 260px; position: relative;">
                    <input type="text" name="search" placeholder="Cari nomor order atau nama proyek..." value="{{ request('search') }}" style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.25rem; border: 1px solid rgba(103, 192, 144, 0.2); border-radius: 8px; outline: none;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                </div>
                
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <select name="status" style="padding: 0.65rem 1rem; border: 1px solid rgba(103, 192, 144, 0.2); border-radius: 8px; background: #fff; cursor: pointer;">
                        <option value="">Semua Pembayaran</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>

                    <button type="submit" class="btn-filter" style="padding: 0.65rem 1.25rem; background: var(--color-teal); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;">
                        Cari
                    </button>
                    
                    @if(request()->has('search') || request()->has('status'))
                        <a href="{{ route('buyer.transactions') }}" style="padding: 0.65rem 1rem; border: 1px solid #cbd5e1; background: #fff; border-radius: 8px; color: #64748b; text-decoration: none;" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- TABEL UTAMA ORDER --}}
        <div class="panel" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.1rem; font-family: var(--font-title); color: var(--color-navy); font-weight: 700; margin: 0;"><i class="fas fa-history"></i> Log Transaksi Masuk</h2>
            </div>

            <div style="overflow-x: auto; width: 100%;">
                @if($orders->count() > 0)
                    <table class="buyer-trx-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 1rem 1.25rem;">ID Transaksi</th>
                                <th style="padding: 1rem 1.25rem;">Tanggal</th>
                                <th style="padding: 1rem 1.25rem;">Nama Proyek</th>
                                <th style="padding: 1rem 1.25rem;">Lokasi</th>
                                <th style="padding: 1rem 1.25rem;">Kuantitas</th>
                                <th style="padding: 1rem 1.25rem;">Total Tagihan</th>
                                <th style="padding: 1rem 1.25rem;">Status Bayar</th>
                                <th style="padding: 1rem 1.25rem;">Status Operasional Proyek</th>
                                <th style="padding: 1rem 1.25rem; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                                <td style="padding: 1rem 1.25rem;"><span class="badge-trx-id">#{{ $order->id }}</span></td>
                                <td style="padding: 1rem 1.25rem; font-size: 0.875rem; color: #475569;">{{ $order->created_at->format('d M Y') }}</td>
                                <td style="padding: 1rem 1.25rem;">
                                    <strong style="display: block;">{{ $order->project->name ?? 'Proyek Terhapus' }}</strong>
                                </td>
                                <td style="padding: 1rem 1.25rem;"><i class="fas fa-map-marker-alt"></i> {{ $order->project->location ?? '-' }}</td>
                                <td style="padding: 1rem 1.25rem;">{{ number_format($order->quantity, 0, ',', '.') }} ton CO₂</td>
                                <td style="padding: 1rem 1.25rem;"><strong style="color: var(--color-teal);">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></td>
                                <td style="padding: 1rem 1.25rem;">
                                    <span class="status-badge-payment {{ $order->status }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem 1.25rem;">
                                    <span class="project-badge-status {{ $order->project_status_class }}">
                                        <i class="" style="font-size: 0.55rem; margin-right: 4px; vertical-align: middle;"></i>
                                        {{ $order->project_status_label }}
                                    </span>
                                </td>
                                <td style="padding: 1rem 1.25rem; text-align: center;">
                                    <button class="btn-open-detail" data-order="{{ json_encode($order) }}" style="background: none; border: 1px solid var(--color-green); color: var(--color-green); padding: 5px 12px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 0.8rem; transition: 0.2s;">
                                        <i class="fas fa-search-plus"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                        <i class="fas fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                        <p style="font-weight: 600; margin: 0;">Belum ada riwayat transaksi</p>
                        <span style="font-size: 0.85rem; color: #94a3b8;">Transaksi pembelian kredit karbon Anda akan tercatat lengkap di sini.</span>
                    </div>
                @endif
            </div>

            {{-- PAGINATION --}}
            @if($orders->hasPages())
                <div style="padding: 1.25rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: center;">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- MODAL POPUP DETAIL TRANSAKSI & STATUS PROYEK --}}
<!-- <div id="buyerModal" class="buyer-modal">
    <div class="buyer-modal-content">
        <div class="modal-header-box">
            <h3><i class="fas fa-file-contract"></i> Rincian & Durasi Proyek</h3>
            <span class="close-buyer-modal">&times;</span>
        </div>
        <div class="modal-body-box" id="buyerModalDetails">
            </div>
    </div>
</div> -->
@endsection

@push('scripts')
<script src="{{ asset('js/buyer-transactions.js') }}"></script>
@endpush