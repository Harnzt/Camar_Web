{{-- Banner untuk buyer yang belum diverifikasi admin: boleh menjelajah, belum boleh checkout. --}}
@php
    $noticeUser = Auth::user();
    $purchaseBlockedReason = $noticeUser?->isBuyer() ? $noticeUser->purchaseBlockedReason() : null;
@endphp

@if($purchaseBlockedReason)
    @php
        $isRejectedNotice = $noticeUser->isRejected();
    @endphp
    <div role="status" data-testid="purchase-verification-notice" style="
        display:flex; gap:12px; align-items:flex-start;
        margin-bottom:24px; padding:14px 18px;
        border:1px solid {{ $isRejectedNotice ? '#fca5a5' : '#f4c95d' }};
        border-radius:12px;
        background:{{ $isRejectedNotice ? '#fef2f2' : '#fff8df' }};
        color:{{ $isRejectedNotice ? '#991b1b' : '#72510d' }};
        font-size:.9rem; line-height:1.5;
    ">
        <i class="fas {{ $isRejectedNotice ? 'fa-circle-xmark' : 'fa-hourglass-half' }}" aria-hidden="true" style="margin-top:3px;"></i>
        <div>
            <strong style="display:block;">{{ $purchaseBlockedReason }}</strong>
            <span>Anda tetap dapat menghitung emisi, melihat proyek, dan mengisi keranjang.</span>
        </div>
    </div>
@endif
