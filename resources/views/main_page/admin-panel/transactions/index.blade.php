@extends('main_page.admin-panel.layout')

@php
    $canIssueCertificates = Auth::user()->hasPermission('certificates.issue');
@endphp
@section('title', $canIssueCertificates ? 'Transaksi & Sertifikat' : 'Transaksi')
@section('page-title', $canIssueCertificates ? 'Transaksi & Penerbitan Sertifikat' : 'Transaksi')

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
        @if($canIssueCertificates)
            Status pembayaran diperbarui otomatis oleh Midtrans. Di sini Anda memverifikasi transaksi yang sudah dibayar lalu menerbitkan sertifikatnya.
        @else
            Mode pemantauan. Status pembayaran diperbarui otomatis oleh Midtrans, dan sertifikat diterbitkan oleh Auditor.
        @endif
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
                    @if($canIssueCertificates)
                        <th>Langkah berikutnya</th>
                    @endif
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
                            @endif
                            @if($order->certificateIssuer)
                                <small>Diterbitkan oleh: {{ $order->certificateIssuer->name }}</small>
                            @endif
                        </td>
                        @if($canIssueCertificates)
                            <td>
                                @php
                                    $nextStep = match ($order->status) {
                                        'paid' => ['verified', 'Verifikasi transaksi'],
                                        'verified' => ['completed', 'Terbitkan sertifikat'],
                                        default => null,
                                    };
                                @endphp

                                @if($nextStep)
                                    <form method="POST" action="{{ route('admin.transactions.update', $order) }}" class="table-form-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $nextStep[0] }}">
                                        <label for="notes-{{ $order->id }}">Catatan (opsional)</label>
                                        <input type="text" id="notes-{{ $order->id }}" name="admin_notes" value="{{ $order->admin_notes }}">
                                        <button type="submit" class="btn btn-primary btn-sm">{{ $nextStep[1] }}</button>
                                    </form>
                                @else
                                    <small>{{ match ($order->status) {
                                        'completed' => 'Sertifikat sudah terbit.',
                                        'pending' => 'Menunggu pembayaran dari Midtrans.',
                                        default => 'Tidak ada langkah untuk status ini.',
                                    } }}</small>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canIssueCertificates ? 6 : 5 }}">
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
