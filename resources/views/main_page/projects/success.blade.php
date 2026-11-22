@extends('main_page.layout.app')

@php
    // Status dibaca dari database (diisi webhook Midtrans), bukan dari redirect popup.
    $paymentState = $paymentState ?? 'waiting';
    $allCertificatesIssued = isset($transactions)
        && collect($transactions)->every(fn ($t) => ! is_array($t) && filled($t->certificate_number));
    $statusCopy = match ($paymentState) {
        'confirmed' => [
            'title' => 'Pembayaran Berhasil! 🌿',
            'page_title' => 'Pembayaran Berhasil',
            'color' => '#1a6b3c',
            'icon_bg' => 'linear-gradient(135deg,#1a6b3c,#2d9c5f)',
            'border' => '#d1fae5',
        ],
        'failed' => [
            'title' => 'Pembayaran Gagal',
            'page_title' => 'Pembayaran Gagal',
            'color' => '#b91c1c',
            'icon_bg' => 'linear-gradient(135deg,#b91c1c,#ef4444)',
            'border' => '#fecaca',
        ],
        default => [
            'title' => 'Menunggu konfirmasi pembayaran',
            'page_title' => 'Menunggu Konfirmasi Pembayaran',
            'color' => '#92400e',
            'icon_bg' => 'linear-gradient(135deg,#d97706,#f59e0b)',
            'border' => '#fde68a',
        ],
    };
@endphp

@section('title', $statusCopy['page_title'])

@if($paymentState === 'waiting')
    @section('head')
        <meta http-equiv="refresh" content="15">
    @endsection
@endif

@section('content')
<main style="padding: 120px 0 80px; min-height: 80vh; background: #f7faf8;">
<div class="container" style="max-width: 680px;">

    @if(session('success'))
    <div class="alert alert-success" style="border-radius:12px; margin-bottom:20px;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('info'))
    <div class="alert alert-info" style="border-radius:12px; margin-bottom:20px;">
        {{ session('info') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger" style="border-radius:12px; margin-bottom:20px;">
        {{ session('error') }}
    </div>
    @endif

    {{-- ── KARTU STATUS PEMBAYARAN ── --}}
    <div style="
        background:#fff; border-radius:20px;
        padding:48px 40px; text-align:center;
        box-shadow:0 8px 40px rgba(26,107,60,.1);
        border:1px solid {{ $statusCopy['border'] }};
    ">
        {{-- Ikon --}}
        <div style="
            width:80px; height:80px; border-radius:50%;
            background:{{ $statusCopy['icon_bg'] }};
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 20px; box-shadow:0 4px 20px rgba(0,0,0,.15);
        ">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                @if($paymentState === 'confirmed')
                    <polyline points="20 6 9 17 4 12"></polyline>
                @elseif($paymentState === 'failed')
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                @else
                    <circle cx="12" cy="12" r="9"></circle>
                    <polyline points="12 7 12 12 15 14"></polyline>
                @endif
            </svg>
        </div>

        <h2 style="font-weight:800; color:{{ $statusCopy['color'] }}; margin:0 0 8px;">{{ $statusCopy['title'] }}</h2>
        <p style="color:#666; font-size:.95rem; margin-bottom:32px; line-height:1.6;">
            @if($paymentState === 'confirmed')
                Terima kasih telah berkontribusi dalam pengurangan emisi karbon.<br>
                @if($allCertificatesIssued)
                    Sertifikat digital kamu sudah terbit dan bisa dilihat di dashboard.
                @else
                    Sertifikat digital akan terbit otomatis setelah proyek terkait disetujui.
                @endif
            @elseif($paymentState === 'failed')
                Pembayaran tidak berhasil atau sudah kedaluwarsa. Silakan buat pesanan baru.
            @else
                Kami belum menerima konfirmasi dari Midtrans. Halaman ini akan memuat ulang otomatis.<br>
                Jika kamu membayar lewat transfer bank atau QRIS, selesaikan pembayaran sesuai instruksi.
            @endif
        </p>

        {{-- ── Daftar Transaksi (Multi) ── --}}
        @isset($transactions)
        <div style="text-align:left; margin-bottom:28px;">
            <h5 style="font-weight:700; font-size:.9rem; margin-bottom:12px; color:#333;">📋 Detail Pesanan</h5>
            @foreach($transactions as $tx)
            @php
                $txObj = is_array($tx) ? (object)$tx : $tx;
            @endphp
            <div style="
                border:1px solid #e8f5e9; border-radius:12px;
                padding:14px 16px; margin-bottom:10px;
                display:flex; justify-content:space-between; align-items:center;
                background:#f7fdf9;
            ">
                <div>
                    <div style="font-weight:700; font-size:.88rem; color:#1a1a1a;">
                        {{ is_array($tx) ? $tx['name'] : ($txObj->project->name ?? 'Proyek') }}
                    </div>
                    <div style="color:#888; font-size:.78rem;">
                        {{ is_array($tx) ? $tx['quantity'] : $txObj->quantity }} ton CO₂
                        @if(is_array($tx) && isset($tx['transaction_code']))
                            · {{ $tx['transaction_code'] }}
                        @elseif(!is_array($tx))
                            · {{ $txObj->transaction_code ?? '' }}
                        @endif
                    </div>
                </div>
                <div style="font-weight:700; color:#1a6b3c; font-size:.9rem;">
                    Rp {{ number_format(is_array($tx) ? $tx['total'] : $txObj->total_price, 0, ',', '.') }}
                </div>
            </div>
            @endforeach

            @php
                $grandTotal = collect($transactions)->sum(fn($t) => is_array($t) ? $t['total'] : $t->total_price);
                $grandTons  = collect($transactions)->sum(fn($t) => is_array($t) ? $t['quantity'] : $t->quantity);
            @endphp
            <div style="
                background:linear-gradient(135deg,#1a6b3c,#2d9c5f);
                color:#fff; border-radius:12px;
                padding:14px 16px; margin-top:12px;
                display:flex; justify-content:space-between; align-items:center;
            ">
                <span style="font-weight:700;">Total Bayar ({{ $grandTons }} ton CO₂)</span>
                <span style="font-weight:800; font-size:1.05rem;">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </span>
            </div>
        </div>
        @endisset

        {{-- ── Transaksi Tunggal ── --}}
        @isset($transaction)
        @php
            $qty      = $transaction->quantity;
            $subtotal = $transaction->price_per_ton * $qty;
            $tax      = $subtotal * 0.11;
            $total    = $subtotal + $tax;
        @endphp
        <div style="text-align:left; margin-bottom:28px;">
            <div style="border:1px solid #e8f5e9; border-radius:12px; padding:16px; background:#f7fdf9;">
                <div style="font-weight:700; font-size:.9rem; margin-bottom:4px;">
                    {{ $transaction->project->name ?? 'Proyek' }}
                </div>
                <div style="color:#888; font-size:.8rem; margin-bottom:8px;">
                    Kode: {{ $transaction->transaction_code }}
                </div>
                <div style="display:flex; justify-content:space-between; font-size:.82rem; color:#555; margin-bottom:3px;">
                    <span>{{ $qty }} ton × Rp {{ number_format($transaction->price_per_ton, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:.82rem; color:#555; margin-bottom:8px;">
                    <span>PPN 11%</span>
                    <span>Rp {{ number_format($tax, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-weight:700; color:#1a6b3c;">
                    <span>Total</span>
                    <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endisset

        {{-- Kontribusi visual --}}
        <div style="
            background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;
            padding:16px; margin-bottom:28px; text-align:center;
        ">
            <div style="font-size:1.5rem; margin-bottom:4px;">🌳</div>
            <p style="margin:0; font-size:.83rem; color:#166534; line-height:1.5;">
                Kontribusimu setara dengan <strong>{{ isset($grandTons) ? $grandTons : (isset($qty) ? $qty : 0) }} pohon</strong> yang diselamatkan selama setahun.
            </p>
        </div>

        {{-- CTA Buttons --}}
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('dashboard') }}" style="
                padding:12px 28px;
                background:linear-gradient(135deg,#1a6b3c,#2d9c5f);
                color:#fff; border-radius:10px;
                text-decoration:none; font-weight:700; font-size:.9rem;
            ">
                📊 Lihat Dashboard
            </a>
            <a href="{{ route('projects.index') }}" style="
                padding:12px 28px;
                background:#fff; color:#1a6b3c;
                border:2px solid #1a6b3c; border-radius:10px;
                text-decoration:none; font-weight:700; font-size:.9rem;
            ">
                🌿 Beli Lagi
            </a>
        </div>
    </div>

</div>
</main>
@endsection
