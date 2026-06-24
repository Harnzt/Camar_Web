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

    <div class="table-wrap">
        <table class="table-hover">
            <thead>
                <tr>
                    <th>Order & Tanggal</th>
                    <th>Proyek & Penjual</th>
                    <th>Pembeli</th>
                    <th>Detail & Total</th>
                    <th>Status Aktual</th>
                    <th>Aksi & Pembaruan Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->order_number }}</strong>
                            <small>{{ $order->created_at?->format('d M Y H:i') ?? '-' }}</small>
                        </td>
                        <td>
                            <strong>{{ $order->project?->name ?? 'Proyek dihapus' }}</strong>
                            <small>{{ $order->project?->seller?->name ?? 'Penjual tidak ditemukan' }}</small>
                        </td>
                        <td>
                            <strong>{{ $order->buyer_name ?? $order->user?->name }}</strong>
                            <small>{{ $order->user?->email ?? '-' }}</small>
                        </td>
                        <td>
                            <strong>{{ number_format($order->quantity, 0, ',', '.') }} ton</strong>
                            <small>Rp {{ number_format($order->total_price, 0, ',', '.') }}</small>
                        </td>
                        <td>
                            <span class="status {{ $order->status }}">{{ $order->status }}</span>
                            @if($order->statusUpdater)
                                <small>Oleh: {{ $order->statusUpdater->name }}</small>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.transactions.update', $order) }}" class="table-form-inline">
                                @csrf @method('PATCH')
                                <select name="status">
                                    @foreach(['pending','paid','verified','completed','cancelled','refunded'] as $status)
                                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="admin_notes" value="{{ $order->admin_notes }}" placeholder="Catatan pembaruan status...">
                                <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">Tidak ada transaksi yang sesuai filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</section>
@endsection

