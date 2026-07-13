@extends('main_page.admin-panel.layout')

@section('title', 'Transaksi & Sertifikat')
@section('page-title', 'Transaksi & Penerbitan Sertifikat')

@section('content')
<section class="panel">
    @php
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Pembayaran Berhasil',
            'verified' => 'Diverifikasi Auditor',
            'completed' => 'Sertifikat Terbit',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan',
        ];
        $canManageTransactions = Auth::user()->hasPermission('transactions.manage');
        $canIssueCertificates = Auth::user()->hasPermission('certificates.issue');
    @endphp

    <form method="GET" class="filter-bar">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Nomor order, pembeli, atau proyek">
        <select name="status">
            <option value="">Semua status</option>
            @foreach($statusLabels as $status => $label)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
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
                            <small>{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</small>
                            @if($order->statusUpdater)
                                <small>Oleh: {{ $order->statusUpdater->name }}</small>
                            @endif
                            @if($order->certificate_number)
                                <small>Sertifikat: {{ $order->certificate_number }}</small>
                            @endif
                            @if($order->certificateIssuer)
                                <small>Diterbitkan oleh: {{ $order->certificateIssuer->name }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusOptions = [$order->status => $statusLabels[$order->status] ?? ucfirst($order->status)];

                                if ($canManageTransactions) {
                                    foreach (['pending', 'paid', 'cancelled', 'refunded'] as $status) {
                                        $statusOptions[$status] = $statusLabels[$status];
                                    }
                                }

                                if ($canIssueCertificates) {
                                    if (in_array($order->status, ['paid', 'verified'], true)) {
                                        $statusOptions['verified'] = $statusLabels['verified'];
                                    }

                                    if ($order->status === 'verified') {
                                        $statusOptions['completed'] = $statusLabels['completed'];
                                    }

                                    if ($order->status === 'completed') {
                                        $statusOptions['completed'] = $statusLabels['completed'];
                                    }
                                }

                                $canSubmit = $canManageTransactions
                                    || ($canIssueCertificates && in_array($order->status, ['paid', 'verified'], true));
                            @endphp

                            @if($canSubmit)
                                <form method="POST" action="{{ route('admin.transactions.update', $order) }}" class="table-form-inline">
                                    @csrf @method('PATCH')
                                    <select name="status">
                                        @foreach($statusOptions as $status => $label)
                                            <option value="{{ $status }}" @selected($order->status === $status)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="admin_notes" value="{{ $order->admin_notes }}" placeholder="Catatan verifikasi atau penerbitan...">
                                    <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
                                </form>
                            @else
                                <small>Tidak ada aksi yang tersedia untuk status ini.</small>
                            @endif
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
