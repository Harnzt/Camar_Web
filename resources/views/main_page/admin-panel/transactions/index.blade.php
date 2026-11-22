@extends('main_page.admin-panel.layout')

@section('title', 'Transaksi')
@section('page-title', 'Transaksi')

@section('content')
<section class="panel">
    @php
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Pembayaran Berhasil',
            'verified' => 'Diverifikasi Auditor',
            'completed' => 'Sertifikat Terbit',
            'failed' => 'Pembayaran Gagal',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan',
        ];
    @endphp

    <div class="alert alert-info">
        Mode pemantauan. Status pembayaran diperbarui otomatis oleh Midtrans, dan sertifikat diterbitkan otomatis oleh sistem setelah pembayaran terkonfirmasi untuk proyek yang sudah disetujui.
    </div>

    <form method="GET" class="filter-bar">
        <label class="filter-field filter-field-grow">
            <span>Cari</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Nomor order, nama pembeli, atau proyek">
        </label>
        <label class="filter-field">
            <span>Status</span>
            <select name="status">
                <option value="">Semua status</option>
                @foreach($statusLabels as $status => $label)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button class="btn btn-primary">Terapkan</button>
    </form>

    <div class="table-wrap">
        <table class="table-hover">
            <thead>
                <tr>
                    <th>Order & Tanggal</th>
                    <th>Proyek</th>
                    <th>Pembeli</th>
                    <th>Jumlah & Total</th>
                    <th>Status</th>
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
                            <small>{{ $order->project?->company_name ?? 'CAMAR' }}</small>
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
                            <span class="status {{ $order->status }}">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                            @if($order->statusUpdater)
                                <small>Oleh: {{ $order->statusUpdater->name }}</small>
                            @endif
                            @if($order->certificate_number)
                                <small>Sertifikat: {{ $order->certificate_number }}</small>
                                <small>Diterbitkan oleh: {{ $order->certificate_issuer_name }}</small>
                            @elseif(in_array($order->status, ['paid', 'verified'], true))
                                <small>Sertifikat belum terbit.</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                @if(request()->filled('search') || request()->filled('status'))
                                    Tidak ada transaksi yang cocok dengan filter ini. <a href="{{ route('admin.transactions.index') }}">Reset filter</a>.
                                @else
                                    Belum ada transaksi. Pesanan pembeli akan muncul di sini setelah checkout.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</section>
@endsection
