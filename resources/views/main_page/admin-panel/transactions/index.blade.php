@extends('main_page.admin-panel.layout')

@section('title', 'Manajemen Transaksi')
@section('page-title', 'Manajemen Status Transaksi')

@section('content')
<section class="panel">
    <form method="GET" class="filter-bar">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Nomor order, pembeli, atau proyek">
        <select name="status">
            <option value="">Semua status</option>
            @foreach(['pending','paid','verified','completed','cancelled','refunded'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Terapkan</button>
    </form>
    <div class="transaction-list">
        @forelse($orders as $order)
            <article class="transaction-card">
                <div class="transaction-main">
                    <div><span class="panel-kicker">{{ $order->order_number }}</span><h3>{{ $order->project?->name ?? 'Proyek dihapus' }}</h3><p>{{ $order->buyer_name ?? $order->user?->name }} · {{ $order->quantity }} ton</p></div>
                    <div class="transaction-amount"><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong><span class="status {{ $order->status }}">{{ $order->status }}</span></div>
                </div>
                <form method="POST" action="{{ route('admin.transactions.update', $order) }}" class="transaction-form">
                    @csrf @method('PATCH')
                    <select name="status">
                        @foreach(['pending','paid','verified','completed','cancelled','refunded'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="admin_notes" value="{{ $order->admin_notes }}" placeholder="Catatan perubahan status">
                    <button class="btn btn-primary btn-sm">Perbarui</button>
                </form>
            </article>
        @empty
            <div class="empty-state">Tidak ada transaksi yang sesuai filter.</div>
        @endforelse
    </div>
    {{ $orders->links() }}
</section>
@endsection
