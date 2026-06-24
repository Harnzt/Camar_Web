@extends('main_page.layout.app')

@section('title', 'Kalkulator Karbon')
@section('description', 'Hitung jejak karbon Anda berdasarkan GHG Protocol, ISO 14064-1:2018, IPCC 2006, dan Pedoman KLHK Buku II Vol.I 2012')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/calculator.css') }}">
@endpush

@section('content')

{{-- =====================================================
     HERO
     ===================================================== --}}
<section class="calculator-hero" id="calculator">
    <div class="calculator-hero-bg">
        <img src="{{ asset('images/gunung0.png') }}" alt="Hero Background">
        <div class="hero-overlay"></div>
    </div>
    <div class="container">
        <div class="calculator-hero-content">
            <span class="hero-chip"><i class="fas fa-leaf"></i> Carbon Footprint Calculator</span>
            <h1 class="calculator-hero-title">Kalkulator Emisi Karbon</h1>
            <p class="calculator-hero-description">
                Estimasi jejak karbon berdasarkan <strong>GHG Protocol · ISO 14064-1:2018 · IPCC 2006 · Buku II Vol I KLHK 2012</strong>
            </p>
        </div>
    </div>
</section>

{{-- =====================================================
     MAIN CALCULATOR
     ===================================================== --}}
<main class="calculator-page">
    <div class="container">

        @if(session('warning'))
            <div role="alert" style="
                margin-bottom:24px;
                padding:14px 18px;
                border:1px solid #f4c95d;
                border-radius:12px;
                background:#fff8df;
                color:#72510d;
                font-weight:600;
            ">
                <i class="fas fa-circle-info" style="margin-right:8px;"></i>
                {{ session('warning') }}
            </div>
        @endif

        {{-- ── SCOPE/STEP TAB HEADER ──────────────────────────────────── --}}
        
        @auth
            @if(Auth::user()->account_category === 'company')
                @php $userRole = 'company'; @endphp
            @else
                @php $userRole = 'personal'; @endphp
            @endif 
        @else
            @php $userRole = 'personal'; @endphp
        @endauth

        {{-- ================================================================
             PERSONAL CALCULATOR
             ================================================================ --}}
        @if($userRole === 'personal')

        <div class="calculator-profile-card">
            <div class="calculator-profile-icon"><i class="fas fa-user"></i></div>
            <div>
                <span class="calculator-profile-kicker">Kalkulator Buyer Individu</span>
                <h2>Hitung emisi berdasarkan GHG Protocol</h2>
                <p>Isi aktivitas pada setiap scope, lalu tinjau hasil dan simpan kalkulasi ke dashboard buyer.</p>
            </div>
            <span class="calculator-profile-status"><i class="fas fa-shield-alt"></i> Data tersimpan aman</span>
        </div>

        {{-- Step Tab Nav: Personal --}}
        <div class="step-tab-nav" id="personalNav">
            <button class="step-tab-btn active" data-step="p1" onclick="switchTab('p',1,4)">
                <span class="tab-number">1</span>
                <span class="tab-label">Scope 1</span>
            </button>
            <div class="tab-connector"></div>
            <button class="step-tab-btn" data-step="p2" onclick="switchTab('p',2,4)">
                <span class="tab-number">2</span>
                <span class="tab-label">Scope 2</span>
            </button>
            <div class="tab-connector"></div>
            <button class="step-tab-btn" data-step="p3" onclick="switchTab('p',3,4)">
                <span class="tab-number">3</span>
                <span class="tab-label">Scope 3</span>
            </button>
            <div class="tab-connector"></div>
            <button class="step-tab-btn" data-step="p4" onclick="switchTab('p',4,4)">
                <span class="tab-number">4</span>
                <span class="tab-label">Hasil</span>
            </button>
        </div>

        {{-- ── PERSONAL STEP 1: ENERGI & KENDARAAN ── --}}
        <div class="step-pane active" id="p-step-1">

            <div class="scope-label-banner scope1-banner">
                <i class="fas fa-fire"></i>
                Scope 1 — Emisi Langsung
                <span>Energi rumah tangga dan kendaraan yang Anda gunakan langsung</span>
            </div>

            {{-- ENERGI RUMAH TANGGA --}}
            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('p_energy')">
                    <div class="module-icon scope-green"><i class="fas fa-home"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-green">Pembakaran Stasioner</div>
                        <h2 class="module-title">Konsumsi Energi Rumah Tangga</h2>
                        <p class="module-subtitle">Bahan bakar dapur & genset · Scope 1</p>
                    </div>
                    <div class="module-preview" id="prev_p_energy">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="p_energy-content">

                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>Faktor emisi: IPCC 2006 Vol.2 T2.2 · Buku II Vol I KLHK 2012 · GWP IPCC AR6</span>
                    </div>

                    <div id="rows_p_energy">
                        <div class="entry-row" data-group="p_energy">
                            <div class="entry-fields">
                                <div class="input-group">
                                    <label class="input-label">Jenis Bahan Bakar</label>
                                    <select class="input-field select-field" name="p_energy_fuel[]" onchange="calcLive()">
                                        <option value="">-- Pilih Bahan Bakar --</option>
                                        <option value="lpg">LPG (Gas Tabung)</option>
                                        <option value="cng">Gas Alam / CNG (Gas Pipa)</option>
                                        <option value="wood">Kayu Bakar / Biomassa</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="input-label">Konsumsi Bulanan</label>
                                    <div class="input-with-unit">
                                        <input type="number" class="input-field" name="p_energy_qty[]" placeholder="0" min="0" step="0.01" oninput="calcLive()">
                                        <span class="input-unit unit-dynamic" data-for="p_energy_fuel">kg/bln</span>
                                    </div>
                                </div>
                                <div class="input-group ef-display-group">
                                    <label class="input-label">Faktor Emisi</label>
                                    <div class="ef-chip" data-ef-group="p_energy">—</div>
                                </div>
                            </div>
                            <button class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <button class="btn-add-row" onclick="addRow('p_energy')">
                        <i class="fas fa-plus"></i> Tambah Bahan Bakar
                    </button>

                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Energi RT:</span>
                        <span class="preview-value" id="sub_p_energy">0</span>
                        <span class="preview-unit">kg CO₂e/tahun</span>
                    </div>
                </div>
            </div>

            {{-- KENDARAAN PRIBADI --}}
            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('p_vehicle')">
                    <div class="module-icon scope-orange"><i class="fas fa-car"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-orange">Pembakaran Bergerak</div>
                        <h2 class="module-title">Kendaraan Pribadi</h2>
                        <p class="module-subtitle">Jarak tempuh bulanan · Scope 1</p>
                    </div>
                    <div class="module-preview" id="prev_p_vehicle">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="p_vehicle-content">

                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>Faktor emisi: IPCC 2006 Vol.2 T3.2.2 · Buku II Vol I KLHK 2012 · berbasis jarak tempuh</span>
                    </div>

                    <div id="rows_p_vehicle">
                        <div class="entry-row" data-group="p_vehicle">
                            <div class="entry-fields">
                                <div class="input-group">
                                    <label class="input-label">Jenis Kendaraan</label>
                                    <select class="input-field select-field" name="p_vehicle_type[]" onchange="updateVehicleFuel(this); calcLive()">
                                        <option value="">-- Pilih Kendaraan --</option>
                                        <option value="car_petrol">Mobil Pribadi (Bensin)</option>
                                        <option value="car_diesel">Mobil Pribadi (Diesel)</option>
                                        <option value="motorcycle">Sepeda Motor</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="input-label">Jenis Bahan Bakar</label>
                                    <select class="input-field select-field" name="p_vehicle_fuel[]" onchange="calcLive()">
                                        <option value="">-- Pilih BBM --</option>
                                        <option value="ron98">Bensin RON98</option>
                                        <option value="ron92">Bensin RON92</option>
                                        <option value="ron90">Bensin RON90</option>
                                        <option value="ron88">Bensin RON88</option>
                                        <option value="listrik">Listrik</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="input-label">Jarak Tempuh Bulanan</label>
                                    <div class="input-with-unit">
                                        <input type="number" class="input-field" name="p_vehicle_km[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                        <span class="input-unit">km/bln</span>
                                    </div>
                                </div>
                                <div class="input-group ef-display-group">
                                    <label class="input-label">Faktor Emisi</label>
                                    <div class="ef-chip" data-ef-group="p_vehicle">—</div>
                                </div>
                            </div>
                            <button class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <button class="btn-add-row" onclick="addRow('p_vehicle')">
                        <i class="fas fa-plus"></i> Tambah Kendaraan
                    </button>

                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Kendaraan:</span>
                        <span class="preview-value" id="sub_p_vehicle">0</span>
                        <span class="preview-unit">kg CO₂e/tahun</span>
                    </div>
                </div>
            </div>

            {{-- LISTRIK PLN --}}
            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('p_electricity')">
                    <div class="module-icon scope-blue"><i class="fas fa-bolt"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-blue">Scope 2</div>
                        <h2 class="module-title">Listrik Jaringan Nasional (PLN)</h2>
                        <p class="module-subtitle">Emisi tidak langsung dari pembelian listrik</p>
                    </div>
                    <div class="module-preview" id="prev_p_electricity">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="p_electricity-content">

                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>Faktor emisi: MEMR Permen 10/2022 · Buku II Vol I KLHK 2012 · GHG Protocol Scope 2</span>
                    </div>

                    <div class="input-row-grid">
                        <div class="input-group">
                            <label class="input-label">Konsumsi Listrik Bulanan</label>
                            <div class="input-with-unit">
                                <input type="number" class="input-field" id="p_elec_kwh" placeholder="0" min="0" step="0.01" oninput="calcLive()">
                                <span class="input-unit">kWh/bln</span>
                            </div>
                            <span class="input-hint">Lihat tagihan PLN Anda</span>
                        </div>
                        <div class="input-group ef-display-group">
                            <label class="input-label">Faktor Emisi</label>
                            <div class="ef-chip">0,8099 kgCO₂e/kWh<br><small>Grid Jawa-Bali · MEMR 2022</small></div>
                        </div>
                    </div>

                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Listrik:</span>
                        <span class="preview-value" id="sub_p_electricity">0</span>
                        <span class="preview-unit">kg CO₂e/tahun</span>
                    </div>
                </div>
            </div>

            {{-- STEP NAV --}}
            <div class="step-pane-nav">
                <div></div>
                <button class="btn-step-next" onclick="switchTab('p',2,4)">
                    Scope 2 — Listrik <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── PERSONAL STEP 2: TRANSPORTASI UMUM ── --}}
        <div class="step-pane" id="p-step-2">

            <div class="scope-label-banner scope2-banner">
                <i class="fas fa-bolt"></i>
                Scope 2 — Emisi Tidak Langsung Energi
                <span>Emisi yang berasal dari konsumsi listrik jaringan</span>
            </div>

            <div id="personal-scope2-modules"></div>

            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('p_transit')">
                    <div class="module-icon scope-teal"><i class="fas fa-bus"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-teal">Scope 3</div>
                        <h2 class="module-title">Transportasi Umum & Dinas</h2>
                        <p class="module-subtitle">Penerbangan, kereta, bus, ojek online</p>
                    </div>
                    <div class="module-preview" id="prev_p_transit">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="p_transit-content">

                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>Faktor emisi: GHG Protocol Scope 3 Cat.6 · DEFRA 2023 · Buku II Vol I KLHK 2012</span>
                    </div>

                    <div id="rows_p_transit">
                        <div class="entry-row" data-group="p_transit">
                            <div class="entry-fields">
                                <div class="input-group">
                                    <label class="input-label">Moda Transportasi</label>
                                    <select class="input-field select-field" name="p_transit_mode[]" onchange="calcLive()">
                                        <option value="">-- Pilih Moda --</option>
                                        <option value="flight_dom">Pesawat Udara (Rute Domestik)</option>
                                        <option value="flight_int_short">Pesawat Udara (Internasional &lt;3700 km)</option>
                                        <option value="flight_int_long">Pesawat Udara (Internasional &gt;3700 km)</option>
                                        <option value="train">Kereta Api / KRL / Jarak Jauh</option>
                                        <option value="bus">Bus Umum / Angkot / Taksi / Ojek Online</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="input-label">Jarak Perjalanan</label>
                                    <div class="input-with-unit">
                                        <input type="number" class="input-field" name="p_transit_km[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                        <span class="input-unit">km</span>
                                    </div>
                                </div>
                                <div class="input-group ef-display-group">
                                    <label class="input-label">Faktor Emisi</label>
                                    <div class="ef-chip" data-ef-group="p_transit">—</div>
                                </div>
                            </div>
                            <button class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <button class="btn-add-row" onclick="addRow('p_transit')">
                        <i class="fas fa-plus"></i> Tambah Perjalanan
                    </button>

                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Transportasi:</span>
                        <span class="preview-value" id="sub_p_transit">0</span>
                        <span class="preview-unit">kg CO₂e</span>
                    </div>
                </div>
            </div>

            <div class="step-pane-nav">
                <button class="btn-step-prev" onclick="switchTab('p',1,4)">
                    <i class="fas fa-arrow-left"></i> Scope 1
                </button>
                <button class="btn-step-next" onclick="switchTab('p',3,4)">
                    Scope 3 <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── PERSONAL STEP 3: PANGAN & SAMPAH ── --}}
        <div class="step-pane" id="p-step-3">

            <div class="scope-label-banner scope3-banner">
                <i class="fas fa-globe"></i>
                Scope 3 — Emisi Tidak Langsung Lainnya
                <span>Transportasi, konsumsi pangan, penggunaan air, dan limbah</span>
            </div>

            <div id="personal-scope3-modules"></div>

            {{-- PANGAN --}}
            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('p_food')">
                    <div class="module-icon scope-green"><i class="fas fa-utensils"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-green">Scope 3</div>
                        <h2 class="module-title">Produk & Konsumsi Pangan</h2>
                        <p class="module-subtitle">Emisi rantai hulu dari konsumsi makanan</p>
                    </div>
                    <div class="module-preview" id="prev_p_food">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="p_food-content">

                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>Faktor emisi: IPCC 2006 Vol.4 AFOLU · FAO 2022 · GHG Protocol Scope 3</span>
                    </div>

                    <div id="rows_p_food">
                        <div class="entry-row" data-group="p_food">
                            <div class="entry-fields">
                                <div class="input-group">
                                    <label class="input-label">Jenis Komoditas</label>
                                    <select class="input-field select-field" name="p_food_type[]" onchange="calcLive()">
                                        <option value="">-- Pilih Pangan --</option>
                                        <option value="beef">Daging Sapi / Kambing</option>
                                        <option value="poultry">Daging Ayam / Unggas</option>
                                        <option value="fish">Ikan / Seafood</option>
                                        <option value="veg">Sayuran & Buah-buahan (Nabati)</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="input-label">Berat Konsumsi Bulanan</label>
                                    <div class="input-with-unit">
                                        <input type="number" class="input-field" name="p_food_kg[]" placeholder="0" min="0" step="0.1" oninput="calcLive()">
                                        <span class="input-unit">kg/bln</span>
                                    </div>
                                </div>
                                <div class="input-group ef-display-group">
                                    <label class="input-label">Faktor Emisi</label>
                                    <div class="ef-chip" data-ef-group="p_food">—</div>
                                </div>
                            </div>
                            <button class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <button class="btn-add-row" onclick="addRow('p_food')">
                        <i class="fas fa-plus"></i> Tambah Komoditas
                    </button>

                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Pangan:</span>
                        <span class="preview-value" id="sub_p_food">0</span>
                        <span class="preview-unit">kg CO₂e/tahun</span>
                    </div>
                </div>
            </div>

            {{-- AIR BERSIH --}}
            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('p_water')">
                    <div class="module-icon scope-blue"><i class="fas fa-tint"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-blue">Scope 3</div>
                        <h2 class="module-title">Penggunaan Air Bersih</h2>
                        <p class="module-subtitle">Air PDAM / sumur terpompa</p>
                    </div>
                    <div class="module-preview" id="prev_p_water">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="p_water-content">
                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>Faktor emisi: GHG Protocol Scope 3 Cat.1 · 0,344 kgCO₂e/m³</span>
                    </div>
                    <div class="input-row-grid">
                        <div class="input-group">
                            <label class="input-label">Volume Penggunaan Air Bulanan</label>
                            <div class="input-with-unit">
                                <input type="number" class="input-field" id="p_water_m3" placeholder="0" min="0" step="0.1" oninput="calcLive()">
                                <span class="input-unit">m³/bln</span>
                            </div>
                        </div>
                        <div class="input-group ef-display-group">
                            <label class="input-label">Faktor Emisi</label>
                            <div class="ef-chip">0,344 kgCO₂e/m³<br><small>GHG Protocol Scope 3 Cat.1</small></div>
                        </div>
                    </div>
                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Air:</span>
                        <span class="preview-value" id="sub_p_water">0</span>
                        <span class="preview-unit">kg CO₂e/tahun</span>
                    </div>
                </div>
            </div>

            {{-- SAMPAH --}}
            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('p_waste')">
                    <div class="module-icon scope-orange"><i class="fas fa-trash-alt"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-orange">Scope 3</div>
                        <h2 class="module-title">Sampah & Limbah Domestik</h2>
                        <p class="module-subtitle">Sampah campuran RT ke TPA</p>
                    </div>
                    <div class="module-preview" id="prev_p_waste">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="p_waste-content">
                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>Faktor emisi: IPCC 2006 Vol.5 · Buku II Vol I KLHK 2012 · metode IPCC Default · 0,52 kgCO₂e/kg</span>
                    </div>
                    <div class="input-row-grid">
                        <div class="input-group">
                            <label class="input-label">Estimasi Berat Sampah Bulanan</label>
                            <div class="input-with-unit">
                                <input type="number" class="input-field" id="p_waste_kg" placeholder="0" min="0" step="0.1" oninput="calcLive()">
                                <span class="input-unit">kg/bln</span>
                            </div>
                        </div>
                        <div class="input-group ef-display-group">
                            <label class="input-label">Faktor Emisi</label>
                            <div class="ef-chip">0,52 kgCO₂e/kg<br><small>IPCC 2006 Vol.5 · Buku II Vol I</small></div>
                        </div>
                    </div>
                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Sampah:</span>
                        <span class="preview-value" id="sub_p_waste">0</span>
                        <span class="preview-unit">kg CO₂e/tahun</span>
                    </div>
                </div>
            </div>

            {{-- HITUNG BUTTON --}}
            <div class="action-buttons">
                <button class="btn-calculate" onclick="calculateAll('personal')">
                    <i class="fas fa-calculator"></i> Hitung Total Jejak Karbon
                </button>
                <button class="btn-reset" onclick="resetCalculator()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>

            <div class="step-pane-nav">
                <button class="btn-step-prev" onclick="switchTab('p',2,4)">
                    <i class="fas fa-arrow-left"></i> Scope 2
                </button>
                <div></div>
            </div>
        </div>

        @endif {{-- end personal --}}


        {{-- ================================================================
             COMPANY CALCULATOR
             ================================================================ --}}
        @if($userRole === 'company')

        {{-- Step Tab Nav: Company --}}
        <div class="step-tab-nav" id="companyNav">
            <button class="step-tab-btn active" data-step="c1" onclick="switchTab('c',1,4)">
                <span class="tab-number">1</span>
                <span class="tab-label">Scope 1</span>
            </button>
            <div class="tab-connector"></div>
            <button class="step-tab-btn" data-step="c2" onclick="switchTab('c',2,4)">
                <span class="tab-number">2</span>
                <span class="tab-label">Scope 2</span>
            </button>
            <div class="tab-connector"></div>
            <button class="step-tab-btn" data-step="c3" onclick="switchTab('c',3,4)">
                <span class="tab-number">3</span>
                <span class="tab-label">Scope 3</span>
            </button>
            <div class="tab-connector"></div>
            <button class="step-tab-btn" data-step="c4" onclick="switchTab('c',4,4)">
                <span class="tab-number">4</span>
                <span class="tab-label">Hasil</span>
            </button>
        </div>

        {{-- ── COMPANY SCOPE 1 ──────────────────────────────────────────── --}}
        <div class="step-pane active" id="c-step-1">

            <div class="scope-label-banner scope1-banner">
                <i class="fas fa-fire"></i>
                Scope 1 — Emisi Langsung
                <span>GHG Protocol Scope 1 · ISO 14064-1:2018 Kl.6.3 · IPCC 2006 Vol.2</span>
            </div>

            {{-- Scope 1 – Sub Step Tab --}}
            <div class="sub-step-tabs">
                <button class="sub-tab-btn active" onclick="switchSubTab('s1', 'stat')">
                    <i class="fas fa-fire-alt"></i> Step 1: Pembakaran Stasioner
                </button>
                <button class="sub-tab-btn" onclick="switchSubTab('s1', 'mobile')">
                    <i class="fas fa-car-side"></i> Step 2: Pembakaran Bergerak
                </button>
            </div>

            {{-- S1 Sub: Stasioner --}}
            <div class="sub-tab-pane active" id="s1-stat">
                <div class="calc-module">
                    <div class="module-header" onclick="toggleModule('c_stat')">
                        <div class="module-icon scope-orange"><i class="fas fa-industry"></i></div>
                        <div class="module-info">
                            <div class="module-badge badge-orange">Stasioner</div>
                            <h2 class="module-title">Pembakaran Stasioner</h2>
                            <p class="module-subtitle">Aset tetap: pabrik, boiler, genset, kantor</p>
                        </div>
                        <div class="module-preview" id="prev_c_stat">0 <span>kg CO₂e</span></div>
                        <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="module-content" id="c_stat-content">

                        <div class="ref-info-bar">
                            <i class="fas fa-info-circle"></i>
                            <span>IPCC 2006 Vol.2 T2.2 · Buku II Vol I KLHK 2012 · GHG Protocol Scope 1</span>
                        </div>

                        <div id="rows_c_stat">
                            <div class="entry-row" data-group="c_stat">
                                <div class="entry-fields">
                                    <div class="input-group">
                                        <label class="input-label">Jenis Bahan Bakar</label>
                                        <select class="input-field select-field" name="c_stat_fuel[]" onchange="updateEfChip(this, 'c_stat'); calcLive()">
                                            <option value="">-- Pilih Bahan Bakar --</option>
                                            <option value="solar_cn53">Minyak Solar CN53</option>
                                            <option value="solar_cn51">Minyak Solar CN51</option>
                                            <option value="solar_cn48">Minyak Solar CN48</option>
                                            <option value="diesel">Minyak Diesel</option>
                                            <option value="ron98">Bensin RON98</option>
                                            <option value="ron92">Bensin RON92</option>
                                            <option value="ron90">Bensin RON90</option>
                                            <option value="ron88">Bensin RON88</option>
                                            <option value="lpg">LPG (Liquid Petroleum Gas)</option>
                                            <option value="coal_bit">Batubara (Bituminous Coal)</option>
                                            <option value="coal_briket">Briket Batubara</option>
                                            <option value="charcoal">Arang</option>
                                            <option value="natgas">Gas Alam</option>
                                            <option value="lgv">LGV</option>
                                            <option value="lng">LNG</option>
                                            <option value="avtur">Avtur</option>
                                            <option value="kerosene">Minyak Tanah</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <label class="input-label">Konsumsi Bahan Bakar</label>
                                        <div class="input-with-unit">
                                            <input type="number" class="input-field" name="c_stat_qty[]" placeholder="0" min="0" step="0.01" oninput="calcLive()">
                                            <span class="input-unit unit-stat">Nm³</span>
                                        </div>
                                    </div>
                                    <div class="input-group ef-display-group">
                                        <label class="input-label">Faktor Emisi</label>
                                        <div class="ef-chip" data-ef-group="c_stat">—</div>
                                    </div>
                                </div>
                                <button class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button>
                            </div>
                        </div>

                        <button class="btn-add-row" onclick="addRow('c_stat')">
                            <i class="fas fa-plus"></i> Tambah Bahan Bakar
                        </button>

                        <div class="scope-preview">
                            <span class="preview-label">Sub-total Stasioner:</span>
                            <span class="preview-value" id="sub_c_stat">0</span>
                            <span class="preview-unit">kg CO₂e/tahun</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- S1 Sub: Mobile --}}
            <div class="sub-tab-pane" id="s1-mobile">
                <div class="calc-module">
                    <div class="module-header" onclick="toggleModule('c_mobile')">
                        <div class="module-icon scope-orange"><i class="fas fa-truck"></i></div>
                        <div class="module-info">
                            <div class="module-badge badge-orange">Bergerak</div>
                            <h2 class="module-title">Pembakaran Bergerak — Kendaraan Operasional</h2>
                            <p class="module-subtitle">Armada kendaraan yang dimiliki/dikontrol perusahaan</p>
                        </div>
                        <div class="module-preview" id="prev_c_mobile">0 <span>kg CO₂e</span></div>
                        <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="module-content" id="c_mobile-content">

                        <div class="ref-info-bar">
                            <i class="fas fa-info-circle"></i>
                            <span>IPCC 2006 Vol.2 T3.2.2 · Buku II Vol I KLHK 2012 · GHG Protocol Scope 1</span>
                        </div>

                        {{-- Sub-sub toggle: pemakaian BBM vs jarak --}}
                        <div class="method-toggle-row">
                            <button class="method-btn active" id="methodFuelBtn" onclick="switchMethod('fuel')">
                                <i class="fas fa-gas-pump"></i> Berdasarkan Pemakaian BBM
                            </button>
                            <button class="method-btn" id="methodDistBtn" onclick="switchMethod('dist')">
                                <i class="fas fa-road"></i> Berdasarkan Jarak Tempuh
                            </button>
                        </div>

                        {{-- Method: BBM --}}
                        <div id="method-fuel" class="method-panel active">
                            <div id="rows_c_mobile_fuel">
                                <div class="entry-row" data-group="c_mobile_fuel">
                                    <div class="entry-fields">
                                        <div class="input-group">
                                            <label class="input-label">Jenis Bahan Bakar</label>
                                            <select class="input-field select-field" name="c_mobile_fuel_type[]" onchange="updateEfChip(this,'c_mobile_fuel'); calcLive()">
                                                <option value="">-- Pilih BBM --</option>
                                                <option value="solar_cn53">Minyak Solar CN53</option>
                                                <option value="solar_cn51">Minyak Solar CN51</option>
                                                <option value="solar_cn48">Minyak Solar CN48</option>
                                                <option value="diesel">Minyak Diesel</option>
                                                <option value="ron98">Bensin RON98</option>
                                                <option value="ron92">Bensin RON92</option>
                                                <option value="ron90">Bensin RON90</option>
                                                <option value="ron88">Bensin RON88</option>
                                                <option value="avtur">Avtur (Jet Kerosene)</option>
                                            </select>
                                        </div>
                                        <div class="input-group">
                                            <label class="input-label">Konsumsi BBM</label>
                                            <div class="input-with-unit">
                                                <input type="number" class="input-field" name="c_mobile_fuel_qty[]" placeholder="0" min="0" step="0.01" oninput="calcLive()">
                                                <span class="input-unit">Liter/thn</span>
                                            </div>
                                        </div>
                                        <div class="input-group ef-display-group">
                                            <label class="input-label">Faktor Emisi</label>
                                            <div class="ef-chip" data-ef-group="c_mobile_fuel">—</div>
                                        </div>
                                    </div>
                                    <button class="btn-remove-row" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button class="btn-add-row" onclick="addRow('c_mobile_fuel')">
                                <i class="fas fa-plus"></i> Tambah Kendaraan
                            </button>
                        </div>

                        {{-- Method: Jarak --}}
                        <div id="method-dist" class="method-panel">
                            <div id="rows_c_mobile_dist">
                                <div class="entry-row" data-group="c_mobile_dist">
                                    <div class="entry-fields">
                                        <div class="input-group">
                                            <label class="input-label">Jenis Bahan Bakar</label>
                                            <select class="input-field select-field" name="c_mobile_dist_fuel[]" onchange="updateEfChip(this,'c_mobile_dist'); calcLive()">
                                                <option value="">-- Pilih BBM --</option>
                                                <option value="solar_cn53">Minyak Solar CN53</option>
                                                <option value="solar_cn51">Minyak Solar CN51</option>
                                                <option value="solar_cn48">Minyak Solar CN48</option>
                                                <option value="diesel">Minyak Diesel</option>
                                                <option value="ron98">Bensin RON98</option>
                                                <option value="ron92">Bensin RON92</option>
                                                <option value="ron90">Bensin RON90</option>
                                                <option value="ron88">Bensin RON88</option>
                                            </select>
                                        </div>
                                        <div class="input-group">
                                            <label class="input-label">Jarak Tempuh</label>
                                            <div class="input-with-unit">
                                                <input type="number" class="input-field" name="c_mobile_dist_km[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                                <span class="input-unit">km/thn</span>
                                            </div>
                                        </div>
                                        <div class="input-group ef-display-group">
                                            <label class="input-label">Faktor Emisi</label>
                                            <div class="ef-chip" data-ef-group="c_mobile_dist">—</div>
                                        </div>
                                    </div>
                                    <button class="btn-remove-row" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button class="btn-add-row" onclick="addRow('c_mobile_dist')">
                                <i class="fas fa-plus"></i> Tambah Kendaraan
                            </button>
                        </div>

                        <div class="scope-preview">
                            <span class="preview-label">Sub-total Bergerak:</span>
                            <span class="preview-value" id="sub_c_mobile">0</span>
                            <span class="preview-unit">kg CO₂e/tahun</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-pane-nav">
                <div></div>
                <button class="btn-step-next" onclick="switchTab('c',2,4)">
                    Scope 2 — Listrik <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── COMPANY SCOPE 2 ──────────────────────────────────────────── --}}
        <div class="step-pane" id="c-step-2">

            <div class="scope-label-banner scope2-banner">
                <i class="fas fa-bolt"></i>
                Scope 2 — Emisi Tidak Langsung Energi
                <span>GHG Protocol Scope 2 · MEMR Permen 10/2022 · Buku II Vol I KLHK 2012</span>
            </div>

            <div class="calc-module">
                <div class="module-header" onclick="toggleModule('c_elec')">
                    <div class="module-icon scope-blue"><i class="fas fa-bolt"></i></div>
                    <div class="module-info">
                        <div class="module-badge badge-blue">Scope 2</div>
                        <h2 class="module-title">Listrik PLN</h2>
                        <p class="module-subtitle">Pilih grid wilayah sesuai lokasi perusahaan</p>
                    </div>
                    <div class="module-preview" id="prev_c_elec">0 <span>kg CO₂e</span></div>
                    <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="module-content" id="c_elec-content">

                    <div class="ref-info-bar">
                        <i class="fas fa-info-circle"></i>
                        <span>MEMR Permen 10/2022 · Buku II Vol I KLHK 2012 · GHG Protocol Scope 2 location-based</span>
                    </div>

                    <div id="rows_c_elec">
                        <div class="entry-row" data-group="c_elec">
                            <div class="entry-fields">
                                <div class="input-group">
                                    <label class="input-label">Sumber Listrik</label>
                                    <select class="input-field select-field" name="c_elec_src[]" onchange="updateEfChip(this,'c_elec'); calcLive()">
                                        <option value="">-- Pilih Grid PLN --</option>
                                        <option value="jawa_bali">Listrik PLN (Jawa-Bali)</option>
                                        <option value="sumatra">Listrik PLN (Sumatra)</option>
                                        <option value="kalimantan">Listrik PLN (Kalimantan)</option>
                                        <option value="sulawesi">Listrik PLN (Sulawesi)</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="input-label">Jumlah Pemakaian</label>
                                    <div class="input-with-unit">
                                        <input type="number" class="input-field" name="c_elec_kwh[]" placeholder="0" min="0" step="0.01" oninput="calcLive()">
                                        <span class="input-unit">kWh/thn</span>
                                    </div>
                                </div>
                                <div class="input-group ef-display-group">
                                    <label class="input-label">Faktor Emisi</label>
                                    <div class="ef-chip" data-ef-group="c_elec">—</div>
                                </div>
                            </div>
                            <button class="btn-remove-row" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <button class="btn-add-row" onclick="addRow('c_elec')">
                        <i class="fas fa-plus"></i> Tambah Sumber Listrik
                    </button>

                    <div class="scope-preview">
                        <span class="preview-label">Sub-total Scope 2:</span>
                        <span class="preview-value" id="sub_c_elec">0</span>
                        <span class="preview-unit">kg CO₂e/tahun</span>
                    </div>
                </div>
            </div>

            <div class="step-pane-nav">
                <button class="btn-step-prev" onclick="switchTab('c',1,4)">
                    <i class="fas fa-arrow-left"></i> Scope 1
                </button>
                <button class="btn-step-next" onclick="switchTab('c',3,4)">
                    Scope 3 <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── COMPANY SCOPE 3 ──────────────────────────────────────────── --}}
        <div class="step-pane" id="c-step-3">

            <div class="scope-label-banner scope3-banner">
                <i class="fas fa-globe"></i>
                Scope 3 — Emisi Nilai Rantai
                <span>GHG Protocol Corporate Value Chain Standard (2011) · DEFRA 2023</span>
            </div>

            {{-- Scope 3 Sub-Step Tabs --}}
            <div class="sub-step-tabs">
                <button class="sub-tab-btn active" onclick="switchSubTab('s3','flight')">
                    <i class="fas fa-plane"></i> Step 1: Pesawat
                </button>
                <button class="sub-tab-btn" onclick="switchSubTab('s3','hotel')">
                    <i class="fas fa-hotel"></i> Step 2: Hotel
                </button>
                <button class="sub-tab-btn" onclick="switchSubTab('s3','train')">
                    <i class="fas fa-train"></i> Step 3: Kereta
                </button>
            </div>

            {{-- S3: Pesawat --}}
            <div class="sub-tab-pane active" id="s3-flight">
                <div class="calc-module">
                    <div class="module-header" onclick="toggleModule('c_flight')">
                        <div class="module-icon scope-teal"><i class="fas fa-plane"></i></div>
                        <div class="module-info">
                            <div class="module-badge badge-teal">Cat.6 Bisnis</div>
                            <h2 class="module-title">Perjalanan Udara Bisnis</h2>
                            <p class="module-subtitle">Penerbangan dinas karyawan</p>
                        </div>
                        <div class="module-preview" id="prev_c_flight">0 <span>kg CO₂e</span></div>
                        <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="module-content" id="c_flight-content">
                        <div class="ref-info-bar">
                            <i class="fas fa-info-circle"></i>
                            <span>GHG Protocol Scope 3 Cat.6 · DEFRA 2023 · kgCO₂e per penumpang·km</span>
                        </div>
                        <div id="rows_c_flight">
                            <div class="entry-row" data-group="c_flight">
                                <div class="entry-fields">
                                    <div class="input-group">
                                        <label class="input-label">Kelas Penerbangan</label>
                                        <select class="input-field select-field" name="c_flight_class[]" onchange="updateEfChip(this,'c_flight'); calcLive()">
                                            <option value="">-- Pilih Kelas --</option>
                                            <option value="economy">Ekonomi</option>
                                            <option value="business">Bisnis</option>
                                            <option value="first">First Class</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <label class="input-label">Jumlah Penumpang</label>
                                        <input type="number" class="input-field" name="c_flight_pax[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                    </div>
                                    <div class="input-group">
                                        <label class="input-label">Jarak Tempuh</label>
                                        <div class="input-with-unit">
                                            <input type="number" class="input-field" name="c_flight_km[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                            <span class="input-unit">km</span>
                                        </div>
                                    </div>
                                    <div class="input-group ef-display-group">
                                        <label class="input-label">Faktor Emisi</label>
                                        <div class="ef-chip" data-ef-group="c_flight">—</div>
                                    </div>
                                </div>
                                <button class="btn-remove-row" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <button class="btn-add-row" onclick="addRow('c_flight')">
                            <i class="fas fa-plus"></i> Tambah Rute Penerbangan
                        </button>
                        <div class="scope-preview">
                            <span class="preview-label">Sub-total Pesawat:</span>
                            <span class="preview-value" id="sub_c_flight">0</span>
                            <span class="preview-unit">kg CO₂e</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- S3: Hotel --}}
            <div class="sub-tab-pane" id="s3-hotel">
                <div class="calc-module">
                    <div class="module-header" onclick="toggleModule('c_hotel')">
                        <div class="module-icon scope-teal"><i class="fas fa-hotel"></i></div>
                        <div class="module-info">
                            <div class="module-badge badge-teal">Cat.6 Hotel</div>
                            <h2 class="module-title">Akomodasi Hotel</h2>
                            <p class="module-subtitle">Penginapan perjalanan dinas</p>
                        </div>
                        <div class="module-preview" id="prev_c_hotel">0 <span>kg CO₂e</span></div>
                        <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="module-content" id="c_hotel-content">
                        <div class="ref-info-bar">
                            <i class="fas fa-info-circle"></i>
                            <span>GHG Protocol Scope 3 Cat.6 · EF: 20,6 kgCO₂e/kamar·malam (DEFRA 2023)</span>
                        </div>
                        <div id="rows_c_hotel">
                            <div class="entry-row" data-group="c_hotel">
                                <div class="entry-fields">
                                    <div class="input-group">
                                        <label class="input-label">Jumlah Hari Menginap</label>
                                        <div class="input-with-unit">
                                            <input type="number" class="input-field" name="c_hotel_nights[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                            <span class="input-unit">malam</span>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <label class="input-label">Jumlah Kamar</label>
                                        <input type="number" class="input-field" name="c_hotel_rooms[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                    </div>
                                    <div class="input-group ef-display-group">
                                        <label class="input-label">Faktor Emisi</label>
                                        <div class="ef-chip">20,6 kgCO₂e/kamar·malam<br><small>DEFRA 2023 · GHG Protocol Cat.6</small></div>
                                    </div>
                                </div>
                                <button class="btn-remove-row" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <button class="btn-add-row" onclick="addRow('c_hotel')">
                            <i class="fas fa-plus"></i> Tambah Hotel
                        </button>
                        <div class="scope-preview">
                            <span class="preview-label">Sub-total Hotel:</span>
                            <span class="preview-value" id="sub_c_hotel">0</span>
                            <span class="preview-unit">kg CO₂e</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- S3: Kereta --}}
            <div class="sub-tab-pane" id="s3-train">
                <div class="calc-module">
                    <div class="module-header" onclick="toggleModule('c_train')">
                        <div class="module-icon scope-teal"><i class="fas fa-train"></i></div>
                        <div class="module-info">
                            <div class="module-badge badge-teal">Cat.6 Kereta</div>
                            <h2 class="module-title">Perjalanan Kereta</h2>
                            <p class="module-subtitle">Perjalanan dinas via kereta api</p>
                        </div>
                        <div class="module-preview" id="prev_c_train">0 <span>kg CO₂e</span></div>
                        <div class="module-toggle"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="module-content" id="c_train-content">
                        <div class="ref-info-bar">
                            <i class="fas fa-info-circle"></i>
                            <span>GHG Protocol Scope 3 Cat.6 · Buku II Vol I KLHK 2012 · kgCO₂e/penumpang·km</span>
                        </div>
                        <div id="rows_c_train">
                            <div class="entry-row" data-group="c_train">
                                <div class="entry-fields">
                                    <div class="input-group">
                                        <label class="input-label">Kelas Kereta</label>
                                        <select class="input-field select-field" name="c_train_class[]" onchange="updateEfChip(this,'c_train'); calcLive()">
                                            <option value="">-- Pilih Kelas --</option>
                                            <option value="ekonomi">Ekonomi</option>
                                            <option value="bisnis">Bisnis</option>
                                            <option value="eksekutif">Eksekutif</option>
                                            <option value="panoramic">Panoramic</option>
                                            <option value="luxury">Luxury</option>
                                            <option value="priority">Priority</option>
                                            <option value="compartment">Compartment</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <label class="input-label">Jarak Tempuh</label>
                                        <div class="input-with-unit">
                                            <input type="number" class="input-field" name="c_train_km[]" placeholder="0" min="0" step="1" oninput="calcLive()">
                                            <span class="input-unit">km</span>
                                        </div>
                                    </div>
                                    <div class="input-group ef-display-group">
                                        <label class="input-label">Faktor Emisi</label>
                                        <div class="ef-chip" data-ef-group="c_train">—</div>
                                    </div>
                                </div>
                                <button class="btn-remove-row" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <button class="btn-add-row" onclick="addRow('c_train')">
                            <i class="fas fa-plus"></i> Tambah Perjalanan Kereta
                        </button>
                        <div class="scope-preview">
                            <span class="preview-label">Sub-total Kereta:</span>
                            <span class="preview-value" id="sub_c_train">0</span>
                            <span class="preview-unit">kg CO₂e</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn-calculate" onclick="calculateAll('company')">
                    <i class="fas fa-calculator"></i> Hitung Total Emisi Perusahaan
                </button>
                <button class="btn-reset" onclick="resetCalculator()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>

            <div class="step-pane-nav">
                <button class="btn-step-prev" onclick="switchTab('c',2,4)">
                    <i class="fas fa-arrow-left"></i> Scope 2
                </button>
                <button class="btn-step-next" onclick="switchTab('c',4,4)">
                    Lihat Hasil <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── COMPANY STEP 4: HASIL ──────────────────────────────────── --}}
        <div class="step-pane" id="c-step-4">
            <div class="scope-label-banner result-banner">
                <i class="fas fa-chart-bar"></i>
                Hasil Kalkulasi Emisi Perusahaan
            </div>

            {{-- Result placeholder (filled by JS) --}}
            <div id="result-box-company" class="result-box"></div>

            <div class="step-pane-nav">
                <button class="btn-step-prev" onclick="switchTab('c',3,4)">
                    <i class="fas fa-arrow-left"></i> Scope 3
                </button>
                <div></div>
            </div>
        </div>

        @endif {{-- end company --}}

        {{-- ── SHARED RESULT BOX (personal) ───────────────────────────── --}}
        @if($userRole === 'personal')
{{-- ── SHARED RESULT BOX (Dinamis & Interaktif) ───────────────────────────── --}}
        <div class="step-pane" id="p-step-4">
        <div class="scope-label-banner result-banner">
            <i class="fas fa-chart-bar"></i>
            Hasil Kalkulasi Emisi Individu
            <span>Ringkasan tahunan berdasarkan seluruh data Scope 1, 2, dan 3</span>
        </div>
        <div class="result-box" id="result-box" style="display:none; margin-top: 30px;">
            <h3 class="result-title"><i class="fas fa-chart-bar"></i> Hasil Kalkulasi Emisi Anda</h3>

            {{-- Grid Angka Ringkasan --}}
            <div class="result-grid">
                <div class="result-item">
                    <div class="result-label">Total Emisi Anda</div>
                    <div class="result-value" id="hasil-emisi">0 kg CO₂e</div>
                    <div class="result-unit">Berdasarkan data input</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Estimasi Biaya Offset</div>
                    <div class="result-value" id="hasil-biaya" style="color: #124170;">Rp 0</div>
                    <div class="result-unit">Mulai dari Rp 150.000 / ton</div>
                </div>
                <div class="result-item result-item-total">
                    <div class="result-label">Rekomendasi Proyek</div>
                    <div class="result-value" id="rekomendasi-proyek" style="font-size: 1.15rem; padding-top: 10px; color: #26667F;">-</div>
                    <div class="result-unit">Proyek mitigasi terverifikasi</div>
                </div>
            </div>

            {{-- Diagram Distribusi Emisi per Kategori --}}
            <div class="distribution-section" id="distribution-personal">
                <div class="distribution-title">Distribusi Emisi per Kategori</div>
                <div id="distribution-bars"></div>
            </div>

            {{-- Footer Informasi Pohon Setara --}}
            <div class="result-footer" id="result-footer-personal">
                <p class="result-info">
                    Setara dengan <strong id="tree-equivalent">0</strong> pohon yang harus ditanam untuk mengompensasi emisi ini selama 30 tahun (Asumsi serapan 21.77 kg CO₂/pohon/tahun).
                </p>
            </div>

            {{-- KONTROL E-COMMERCE: Tombol Aksi Pembelian & Penyimpanan --}}
            <div class="save-action">
                @Auth
                    <button class="btn-calculate" onclick="saveCalculationToDatabase(event)" style="display:inline-flex; align-items:center; gap:0.5rem; cursor:pointer;">
                        <i class="fas fa-cloud-upload-alt"></i> Simpan Hasil ke Dashboard
                    </button>
                @else
                    {{-- Jika statusnya Guest, kunci tombol simpan dan arahkan ke halaman register/login --}}
                    <a href="{{ route('register') }}" class="btn-calculate" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; background: linear-gradient(135deg, #E8734A, #c05a2e);">
                        <i class="fas fa-user-plus"></i> Daftar Akun untuk Simpan Hasil
                    </a>
                @endauth
                
                <a href="{{ route('projects.index') }}" class="btn-calculate" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-seedling"></i> Cari Proyek & Lakukan Offset
                </a>
                <button class="btn-reset" onclick="resetCalculator()">
                    <i class="fas fa-redo"></i> Hitung Ulang
                </button>
            </div>

            <div class="result-disclaimer">
                <i class="fas fa-info-circle"></i>
                Kalkulasi menggunakan faktor emisi dari GHG Protocol, ISO 14064-1:2018, IPCC 2006 Guidelines, dan Pedoman Inventarisasi GRK Nasional Buku II Volume I KLHK 2012.
            </div>
        </div>
        <div class="step-pane-nav">
            <button class="btn-step-prev" onclick="switchTab('p',3,4)">
                <i class="fas fa-arrow-left"></i> Scope 3
            </button>
            <div></div>
        </div>
        </div>
        @endif

        {{-- ── REFERENSI FAKTOR EMISI ──────────────────────────────────── --}}
        <div class="factors-reference">
            <h3><i class="fas fa-book-open"></i> Referensi Faktor Emisi</h3>
            <div class="factors-grid">
                <div class="factor-item">
                    <h4><i class="fas fa-gas-pump"></i> Bahan Bakar Cair</h4>
                    <ul>
                        <li>Solar CN53/51/48: <strong>2,63–2,68</strong> kgCO₂e/L</li>
                        <li>Bensin RON98: <strong>2,19</strong> kgCO₂e/L</li>
                        <li>Bensin RON92/90/88: <strong>2,20–2,24</strong> kgCO₂e/L</li>
                        <li>Avtur: <strong>2,55</strong> kgCO₂e/L</li>
                        <li>Minyak Tanah: <strong>2,54</strong> kgCO₂e/L</li>
                    </ul>
                    <p class="factor-ref">IPCC 2006 Vol.2 T2.2 · Buku II Vol I 2012</p>
                </div>
                <div class="factor-item">
                    <h4><i class="fas fa-fire"></i> Gas & Padat</h4>
                    <ul>
                        <li>LPG: <strong>2,983</strong> kgCO₂e/kg</li>
                        <li>Gas Alam: <strong>2,150</strong> kgCO₂e/m³</li>
                        <li>LNG: <strong>2,75</strong> kgCO₂e/kg</li>
                        <li>Batubara Bituminous: <strong>2,393</strong> kgCO₂e/kg</li>
                        <li>Kayu Bakar: <strong>1,747</strong> kgCO₂e/kg</li>
                    </ul>
                    <p class="factor-ref">IPCC 2006 Vol.2 T2.2 · GWP IPCC AR6</p>
                </div>
                <div class="factor-item">
                    <h4><i class="fas fa-bolt"></i> Listrik PLN</h4>
                    <ul>
                        <li>Jawa-Bali: <strong>0,8099</strong> kgCO₂e/kWh</li>
                        <li>Sumatra: <strong>0,8790</strong> kgCO₂e/kWh</li>
                        <li>Kalimantan: <strong>1,0460</strong> kgCO₂e/kWh</li>
                        <li>Sulawesi: <strong>0,8450</strong> kgCO₂e/kWh</li>
                    </ul>
                    <p class="factor-ref">MEMR Permen 10/2022 · Buku II Vol I 2012</p>
                </div>
                <div class="factor-item">
                    <h4><i class="fas fa-plane"></i> Transportasi</h4>
                    <ul>
                        <li>Pesawat Ekonomi: <strong>0,133</strong> kgCO₂e/pax·km</li>
                        <li>Pesawat Bisnis: <strong>0,266</strong> kgCO₂e/pax·km</li>
                        <li>Pesawat First: <strong>0,399</strong> kgCO₂e/pax·km</li>
                        <li>Kereta: <strong>0,037</strong> kgCO₂e/pax·km</li>
                        <li>Hotel: <strong>20,6</strong> kgCO₂e/kamar·malam</li>
                    </ul>
                    <p class="factor-ref">DEFRA 2023 · GHG Protocol Scope 3 Cat.6</p>
                </div>
            </div>
        </div>

    </div>
</main>

@endsection

@push('scripts')
<script>
    // Definisikan key SEBELUM file calculator.js dimuat agar terbaca sempurna
    window.CARBON_STORAGE_KEY = @auth "{{ 'carbon_history_user_' . Auth::id() }}" @else null @endauth;
</script>

<script src="{{ asset('js/calculator.js') }}"></script>
@endpush
