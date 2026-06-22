@extends('main_page.layout.app')

@section('title', 'Proyek Carbon Offset')
@section('description', 'Proyek Carbon Offset - Pilih proyek verified carbon offset untuk mengurangi jejak karbon perusahaan Anda')

@section('content')
<main style="padding: 100px 0 60px;">
<div class="container" style="max-width: 1100px;">

    {{-- URL untuk JS --}}
    <meta name="orders-store-url" content="{{ route('orders.store') }}">

    <h2 style="font-weight:700; margin-bottom:4px;">Keranjang Belanja</h2>
    <p style="color:#666; margin-bottom:32px;">Kredit karbon yang akan kamu beli</p>

    @if(empty($cart))
        {{-- ── EMPTY STATE ── --}}
        <div style="text-align:center; padding:80px 20px; background:#fff; border-radius:16px; border:1px solid #e2e8f0;">
            <div style="font-size:4rem; margin-bottom:16px;">🛒</div>
            <h3 style="margin:0 0 8px; color:#333;">Keranjang kamu kosong</h3>
            <p style="color:#888; margin-bottom:24px;">Tambahkan kredit karbon dari proyek-proyek pilihan kami</p>
            <a href="{{ route('projects.index') }}" style="
                display:inline-block; padding:12px 28px;
                background:linear-gradient(135deg,#1a6b3c,#2d9c5f);
                color:#fff; border-radius:10px; text-decoration:none; font-weight:600;
            ">Jelajahi Proyek</a>
        </div>

    @else
        <div style="display:grid; grid-template-columns:1fr 360px; gap:28px; align-items:start;">

            {{-- ── KIRI: DAFTAR ITEM ── --}}
            <div>

                {{-- Select All Bar --}}
                <div style="
                    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
                    padding:12px 18px; margin-bottom:14px;
                    display:flex; align-items:center; justify-content:space-between;
                    box-shadow:0 1px 4px rgba(0,0,0,.04);
                ">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600; font-size:.9rem; color:#333;">
                        <input type="checkbox" id="selectAll"
                               style="width:18px; height:18px; accent-color:#1a6b3c; cursor:pointer;">
                        Pilih Semua ({{ count($cart) }} produk)
                    </label>
                    <span id="selectedCount" style="font-size:.8rem; color:#888;">0 dipilih</span>
                </div>

                {{-- Cart Items --}}
                <div id="cartItems" style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
                    @foreach($cart as $id => $item)
                    <div class="cart-card" data-item-id="{{ $id }}" style="
                        background:#fff; border:2px solid #e2e8f0;
                        border-radius:16px; overflow:hidden;
                        display:flex; gap:0;
                        box-shadow:0 2px 8px rgba(0,0,0,.05);
                        transition: border-color .2s, opacity .3s, transform .3s;
                    ">
                        {{-- Checkbox kolom kiri --}}
                        <div style="
                            display:flex; align-items:center; justify-content:center;
                            padding:0 14px; flex-shrink:0;
                            border-right:1px solid #f0f0f0;
                        ">
                            <input type="checkbox"
                                   class="item-checkbox"
                                   data-item-id="{{ $id }}"
                                   style="width:18px; height:18px; accent-color:#1a6b3c; cursor:pointer;">
                        </div>

                        {{-- Gambar --}}
                        <img src="{{ asset('images/' . $item['image']) }}"
                             alt="{{ $item['name'] }}"
                             style="width:120px; height:110px; object-fit:cover; flex-shrink:0;"
                             onerror="this.src='https://placehold.co/120x110/1a6b3c/white?text='">

                        {{-- Info & kontrol --}}
                        <div style="padding:14px 16px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <h5 class="card-title" style="margin:0 0 3px; font-size:.92rem; font-weight:700; color:#1a1a1a;">
                                    {{ $item['name'] }}
                                </h5>
                                <p style="margin:0; color:#888; font-size:.78rem;">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }} / ton CO₂
                                </p>
                            </div>

                            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:10px;">
                                {{-- Qty control --}}
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <button onclick="CART.changeQty('{{ $id }}', -1)" style="
                                        width:28px; height:28px; border-radius:50%;
                                        border:1px solid #e2e8f0; background:#f8fafc;
                                        cursor:pointer; font-size:1rem; line-height:1;
                                    ">−</button>
                                    <input id="qty-{{ $id }}"
                                           type="number" value="{{ $item['quantity'] }}"
                                           min="1" max="9999"
                                           onchange="CART.updateQty('{{ $id }}', this.value)"
                                           style="width:48px; text-align:center; border:1px solid #e2e8f0; border-radius:8px; padding:4px; font-size:.88rem;">
                                    <button onclick="CART.changeQty('{{ $id }}', 1)" style="
                                        width:28px; height:28px; border-radius:50%;
                                        border:1px solid #e2e8f0; background:#f8fafc;
                                        cursor:pointer; font-size:1rem; line-height:1;
                                    ">+</button>
                                    <span style="color:#888; font-size:.75rem;">ton CO₂</span>
                                </div>

                                {{-- Subtotal & hapus --}}
                                <div style="text-align:right;">
                                    <div id="subtotal-{{ $id }}" style="font-weight:700; color:#1a6b3c; font-size:.92rem;">
                                        Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                    </div>
                                    <button onclick="CART.removeItem('{{ $id }}')" style="
                                        background:none; border:none; color:#e74c3c;
                                        font-size:.75rem; cursor:pointer; padding:0; margin-top:3px;
                                    ">🗑 Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Action Bar Bawah --}}
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <button onclick="CART.removeSelected()" id="btnRemoveSelected"
                            disabled
                            style="
                                background:none; border:1px solid #e74c3c; color:#e74c3c;
                                padding:8px 16px; border-radius:8px; cursor:pointer;
                                font-size:.825rem; opacity:.4; transition:opacity .2s;
                            ">🗑 Hapus Dipilih</button>

                    <button id="btnClearAll" style="
                        background:none; border:1px solid #ccc; color:#888;
                        padding:8px 16px; border-radius:8px; cursor:pointer;
                        font-size:.825rem;
                    ">Hapus Semua</button>
                </div>
            </div>

            {{-- ── KANAN: RINGKASAN ── --}}
            <div style="position:sticky; top:90px;">
                <div style="
                    background:#fff; border:1px solid #e2e8f0;
                    border-radius:16px; padding:22px;
                    box-shadow:0 2px 8px rgba(0,0,0,.06);
                ">
                    <h5 style="margin:0 0 16px; font-weight:700;">Ringkasan Pesanan</h5>

                    {{-- Item yang dipilih --}}
                    <div id="selectedItemsSummary" style="
                        background:#f8fafc; border-radius:10px; padding:12px;
                        margin-bottom:14px; min-height:48px;
                        font-size:.8rem; color:#555;
                    ">
                        <div id="noSelectionMsg" style="color:#aaa; text-align:center; padding:6px 0;">
                            Pilih produk yang ingin dibeli
                        </div>
                        <div id="selectedItemsList" style="display:none; flex-direction:column; gap:6px;"></div>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:.875rem;">
                        <span style="color:#555;">Total Ton</span>
                        <span id="totalTon" style="font-weight:600;">0 ton</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:.875rem;">
                        <span style="color:#555;">Subtotal</span>
                        <span id="subtotalDisplay">Rp 0</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:.875rem;">
                        <span style="color:#555;">PPN 11%</span>
                        <span id="taxDisplay">Rp 0</span>
                    </div>

                    <hr style="border-color:#f0f0f0; margin:14px 0;">

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                        <span style="font-weight:700;">Total Bayar</span>
                        <span id="totalDisplay" style="font-weight:800; font-size:1.15rem; color:#1a6b3c;">Rp 0</span>
                    </div>

                    <button id="btnCheckout" disabled style="
                        width:100%; padding:13px;
                        background:linear-gradient(135deg,#1a6b3c,#2d9c5f);
                        color:#fff; border:none; border-radius:10px;
                        font-size:.95rem; font-weight:700; cursor:pointer;
                        display:flex; align-items:center; justify-content:center; gap:8px;
                        opacity:.45; transition:opacity .2s;
                    ">
                        Lanjut Bayar
                        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <a href="{{ route('orders.store') }}" style="
                        display:block; text-align:center; margin-top:10px;
                        color:#888; font-size:.82rem; text-decoration:none;
                    ">Kembali Belanja</a>
                </div>

                <div style="
                    margin-top:12px; padding:12px 14px;
                    background:#f0faf4; border-radius:10px;
                    font-size:.78rem; color:#2e7d32; line-height:1.5;
                ">
                    Setiap ton CO₂ yang kamu beli berkontribusi nyata pada proyek konservasi Indonesia.
                </div>
            </div>

        </div>
    @endif

</div>
</main>

 <meta name="csrf-token" content="{{ csrf_token() }}">

@push('scripts')
<script src="{{ asset('js/cart.js') }}"></script>
@endpush

@endsection