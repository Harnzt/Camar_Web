@extends('main_page.layout.app')

@section('title', 'Dashboard Seller - CAMAR')
@section('description', 'Kelola proyek dan pantau penjualan kredit karbon Anda di CAMAR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-seller.css') }}">
@endpush

@section('content')

<div class="seller-wrapper">

    {{-- =====================================================
         TOPBAR
         ===================================================== --}}
    <div class="seller-topbar">
        <div class="seller-topbar-inner">

            <div class="topbar-left">
                <div class="seller-avatar">
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                    <span class="seller-online-dot"></span>
                </div>
                <div class="seller-identity">
                    <span class="seller-greeting">Dashboard Seller</span>
                    <h1 class="seller-name">{{ Auth::user()->name }}</h1>
                    <div class="seller-meta">
                        @if(Auth::user()->company_name)
                            <span class="meta-chip company">
                                <i class="fas fa-building"></i>
                                {{ Auth::user()->company_name }}
                            </span>
                        @endif
                        <span class="meta-chip role">Seller</span>
                        @if(Auth::user()->isVerified())
                            <span class="meta-chip verified">
                                <i class="fas fa-shield-alt"></i> Terverifikasi
                            </span>
                        @else
                            <span class="meta-chip pending">
                                <i class="fas fa-clock"></i> Menunggu Verifikasi
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="topbar-right">
                {{-- Alert Pending Pembayaran --}}
                @if($pendingCount > 0)
                <div class="pending-alert">
                    <i class="fas fa-bell"></i>
                    <div>
                        <strong>{{ $pendingCount }} transaksi menunggu</strong>
                        <span>Rp {{ number_format($pendingAmount, 0, ',', '.') }} belum dikonfirmasi</span>
                    </div>
                </div>
                @endif

                <a href="#upload-proyek" class="btn-add-project">
                    <i class="fas fa-plus"></i>
                    Tambah Proyek
                </a>
            </div>

        </div>
    </div>

    <

        {{-- =====================================================
             ROW 4 — Upload Proyek Baru + Metodologi
             ===================================================== --}}
        <div class="seller-row row-tools" id="upload-proyek">

            {{-- Upload Proyek Baru --}}
            <div class="panel panel-upload">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-plus-circle"></i>
                        Tambah Proyek Baru
                    </h2>
                </div>
                <div class="panel-body">
                    <!-- <form id="newProjectForm" action="#" method="POST" enctype="multipart/form-data"> -->
                        <form action="{{ route('seller.projects.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Nama Proyek <span class="req">*</span></label>
                                <input type="text" name="name" placeholder="Masukkan Nama Proyek" required>
                            </div>
                            <div class="form-group">
                                <label>Kategori <span class="req">*</span></label>
                                <select name="category" required>
                                    <option value="">Pilih kategori</option>
                                    <option value="mangrove">Mangrove</option>
                                    <option value="forest">Forest</option>
                                    <option value="solar">Solar Panel</option>
                                    <option value="wind">Wind Energy</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Standar Sertifikasi</label>
                                <select name="standard">
                                    <option value="">Pilih standar</option>
                                    <option value="Verra VCS">Verra VCS</option>
                                    <option value="Gold Standard">Gold Standard</option>
                                    <option value="Plan Vivo">Plan Vivo</option>
                                    <option value="CAR">CAR</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Lokasi <span class="req">*</span></label>
                                <input type="text" name="location" placeholder="Masukkan Lokasi" required>
                            </div>
                            <div class="form-group">
                                <label>Harga per Ton (Rp) <span class="req">*</span></label>
                                <input type="number" name="price_per_ton" placeholder="0" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>Stok Awal (ton) <span class="req">*</span></label>
                                <input type="number" name="stock_available" placeholder="0" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>CO₂ per Tahun (ton)</label>
                                <input type="number" name="co2_per_year" placeholder="0" min="0" step="0.1">
                            </div>
                            <div class="form-group">
                                <label>Luas Area (ha)</label>
                                <input type="number" name="area_ha" placeholder="0" min="0" step="0.1">
                            </div>
                            <div class="form-group">
                                <label>Keluarga Terdampak</label>
                                <input type="number" name="families_impacted" placeholder="0" min="0">
                            </div>
                            <div class="form-group">
                                <label>Durasi (bulan)</label>
                                <input type="number" name="duration_months" placeholder="0" min="1">
                            </div>
                            <div class="form-group full">
                                <label>Deskripsi Proyek <span class="req">*</span></label>
                                <textarea name="description" rows="3"
                                    placeholder="Jelaskan proyek Anda secara singkat..." required></textarea>
                            </div>
                            <div class="form-group full">
                                <label>Metodologi</label>
                                <textarea name="methodology" rows="2"
                                    placeholder="Contoh: VM0007 - REDD+ Methodology for Avoided Unplanned Deforestation"></textarea>
                            </div>
                            <div class="form-group full">
                                <label>Foto Proyek</label>
                                <div class="file-drop" id="fileDrop"
                                     onclick="document.getElementById('projectImage').click()">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Klik atau drag & drop foto proyek</span>
                                    <small>JPG, PNG — Maks 5MB</small>
                                </div>
                                <input type="file" id="projectImage" name="image"
                                       accept="image/*" style="display:none;">
                            </div>
                        </div>
                        <button type="submit" class="btn-submit-project">
                            <i class="fas fa-leaf"></i>
                            Daftarkan Proyek
                        </button>
                    </form>
                </div>
            </div>

            {{-- Metodologi & Dokumen --}}
            <div class="panel panel-docs">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <i class="fas fa-file-alt"></i>
                        Dokumen & Metodologi
                    </h2>
                </div>
                <div class="panel-body">
                    <p class="docs-desc">
                        Upload dokumen pendukung untuk meningkatkan kepercayaan buyer terhadap proyek Anda.
                        Dokumen yang lengkap meningkatkan konversi penjualan secara signifikan.
                    </p>

                    <div class="doc-upload-list">

                        <div class="doc-upload-item">
                            <div class="doc-upload-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="doc-upload-info">
                                <span class="doc-upload-name">Dokumen Metodologi</span>
                                <span class="doc-upload-hint">PDF — VM0007, AMS-III.D, dll</span>
                            </div>
                            <label class="doc-upload-btn">
                                <i class="fas fa-upload"></i> Upload
                                <input type="file" accept=".pdf" style="display:none;">
                            </label>
                        </div>

                        <div class="doc-upload-item">
                            <div class="doc-upload-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div class="doc-upload-info">
                                <span class="doc-upload-name">Sertifikat Verifikasi</span>
                                <span class="doc-upload-hint">Gold Standard / Verra VCS</span>
                            </div>
                            <label class="doc-upload-btn">
                                <i class="fas fa-upload"></i> Upload
                                <input type="file" accept=".pdf,.jpg,.png" style="display:none;">
                            </label>
                        </div>

                        <div class="doc-upload-item">
                            <div class="doc-upload-icon">
                                <i class="fas fa-map"></i>
                            </div>
                            <div class="doc-upload-info">
                                <span class="doc-upload-name">Peta Lokasi / Shapefile</span>
                                <span class="doc-upload-hint">PDF, KML, atau gambar peta</span>
                            </div>
                            <label class="doc-upload-btn">
                                <i class="fas fa-upload"></i> Upload
                                <input type="file" accept=".pdf,.kml,.jpg,.png" style="display:none;">
                            </label>
                        </div>

                        <div class="doc-upload-item">
                            <div class="doc-upload-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="doc-upload-info">
                                <span class="doc-upload-name">Laporan MRV</span>
                                <span class="doc-upload-hint">Monitoring, Reporting & Verification</span>
                            </div>
                            <label class="doc-upload-btn">
                                <i class="fas fa-upload"></i> Upload
                                <input type="file" accept=".pdf" style="display:none;">
                            </label>
                        </div>

                    </div>

                    {{-- Tips kepercayaan --}}
                    <div class="trust-tips">
                        <div class="trust-tips-title">
                            <i class="fas fa-lightbulb"></i> Tips Tingkatkan Kepercayaan Buyer
                        </div>
                        <ul>
                            <li>Lengkapi semua dokumen sertifikasi</li>
                            <li>Upload foto lapangan terbaru</li>
                            <li>Cantumkan dampak sosial (keluarga terdampak)</li>
                            <li>Sertakan laporan verifikasi terbaru</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>{{-- /seller-body --}}
</div>{{-- /seller-wrapper --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const SELLER_DATA = {
        chartLabels  : {!! json_encode($chartLabels) !!},
        chartRevenue : {!! json_encode($chartRevenue) !!},
        chartCarbon  : {!! json_encode($chartCarbon) !!},
    };
</script>
<script src="{{ asset('js/dashboard-seller.js') }}"></script>
@endpush