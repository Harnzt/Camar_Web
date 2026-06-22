@extends('main_page.layout.app')

@section('title', 'Tentang Kami')
@section('description', 'Tentang CAMAR - Platform Carbon Offset terpercaya di Indonesia')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tentang.css') }}">
@endpush

@section('content')
<!-- Hero Section -->
<section class="tentang-hero" id="tentang">
    <div class="tentang-hero-bg">
        <img src="{{ asset('images/forest0.png') }}" alt="Hero Background">
    </div>
    <div class="container">
        <div class="tentang-hero-content">
            <h1 class="tentang-hero-title">Tentang CAMAR</h1>
            <p class="tentang-hero-description">
                Platform Carbon Offset terpercaya yang menghubungkan perusahaan dengan proyek pengurangan emisi terverifikasi untuk masa depan berkelanjutan
            </p>
        </div>
    </div>
</section>

<!-- Section Divider -->
<div class="section-divider-90"></div>

<!-- Mission & Vision Section -->
<section class="mission-vision-section">
    <div class="container">
        <div class="row g-4">
            <!-- Mission -->
            <div class="col-lg-6">
                <div class="mv-card mission-card">
                    <div class="mv-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h2 class="mv-title">Misi Kami</h2>
                    <p class="mv-text">
                        Memfasilitasi aksi iklim nyata melalui platform marketplace carbon offset yang transparan, terverifikasi, dan mudah diakses untuk perusahaan di Indonesia mencapai target Net-Zero emissions.
                    </p>
                    <ul class="mv-list">
                        <li><i class="fas fa-check-circle"></i> Menyediakan kredit karbon terverifikasi internasional</li>
                        <li><i class="fas fa-check-circle"></i> Mendukung proyek berkelanjutan lokal Indonesia</li>
                        <li><i class="fas fa-check-circle"></i> Transparansi penuh dalam setiap transaksi</li>
                    </ul>
                </div>
            </div>

            <!-- Vision -->
            <div class="col-lg-6">
                <div class="mv-card vision-card">
                    <div class="mv-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h2 class="mv-title">Visi Kami</h2>
                    <p class="mv-text">
                        Menjadi platform carbon offset terdepan di Indonesia yang mengakselerasi transisi menuju ekonomi rendah karbon melalui solusi berbasis teknologi dan kemitraan strategis.
                    </p>
                    <ul class="mv-list">
                        <li><i class="fas fa-check-circle"></i> Indonesia Net-Zero 2060</li>
                        <li><i class="fas fa-check-circle"></i> Ekosistem karbon yang adil dan inklusif</li>
                        <li><i class="fas fa-check-circle"></i> Leadership dalam aksi iklim regional</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Divider -->
<div class="section-divider-90"></div>

<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Nilai-Nilai Kami</h2>
            <p class="section-subtitle">Fondasi yang membimbing setiap keputusan dan tindakan kami</p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-shield-check"></i>
                </div>
                <h3 class="value-title">Integritas</h3>
                <p class="value-text">Transparansi dan kejujuran dalam setiap transaksi dan kemitraan</p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 class="value-title">Inovasi</h3>
                <p class="value-text">Menggunakan teknologi terkini untuk solusi iklim yang efektif</p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="value-title">Kolaborasi</h3>
                <p class="value-text">Membangun kemitraan yang kuat dengan semua stakeholder</p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3 class="value-title">Keberlanjutan</h3>
                <p class="value-text">Fokus pada dampak jangka panjang untuk generasi mendatang</p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h3 class="value-title">Keadilan</h3>
                <p class="value-text">Memastikan akses yang adil terhadap pasar karbon</p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="value-title">Dampak Nyata</h3>
                <p class="value-text">Mengutamakan hasil yang terukur dan dapat diverifikasi</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Divider -->
<div class="section-divider-90"></div>

<!-- Why Choose Section -->
<section class="why-choose-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Mengapa Memilih CAMAR?</h2>
            <p class="section-subtitle">Keunggulan platform kami untuk perjalanan Net-Zero Anda</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-number">01</div>
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="feature-title">Verifikasi Internasional</h3>
                    <p class="feature-text">Semua proyek diverifikasi oleh Gold Standard dan Verra VCS</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-number">02</div>
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="feature-title">Dashboard Transparan</h3>
                    <p class="feature-text">Monitor impact dan tracking carbon offset secara real-time</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="feature-number">03</div>
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 class="feature-title">Support Expert</h3>
                    <p class="feature-text">Tim ahli siap membantu strategy dan implementasi</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Divider -->
<div class="section-divider-90"></div>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Pertanyaan Umum</h2>
            <p class="section-subtitle">Jawaban untuk pertanyaan yang sering diajukan</p>
        </div>

        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Apa itu carbon offset?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Carbon offset adalah pengurangan emisi gas rumah kaca yang dibuat untuk mengompensasi emisi yang dihasilkan di tempat lain. Ini dilakukan dengan mendukung proyek yang mengurangi, menghindari, atau menyerap emisi CO₂.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Bagaimana cara kerja platform CAMAR?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>CAMAR menghubungkan pembeli (perusahaan) dengan penjual (pemilik proyek carbon offset). Platform kami menyediakan marketplace transparan dengan semua proyek terverifikasi internasional.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Apa saja jenis proyek yang tersedia?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Kami menawarkan berbagai jenis proyek termasuk reforestasi, energi terbarukan (solar, wind, hydro), konservasi hutan, dan proyek efisiensi energi. Semua proyek telah diverifikasi dan memenuhi standar internasional.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Berapa biaya untuk menggunakan platform CAMAR?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Registrasi di platform CAMAR gratis. Kami hanya mengambil komisi kecil dari setiap transaksi untuk memastikan keberlangsungan dan pengembangan platform.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section-tentang">
    <div class="container text-center">
        <h2 class="cta-title">Siap Bergabung dengan CAMAR?</h2>
        <p class="cta-description">
            Mulai perjalanan Net-Zero perusahaan Anda bersama platform carbon offset terpercaya di Indonesia
        </p>
        <div class="cta-buttons">
            <a href="{{ route('register') }}" class="btn-cta btn-primary">
                <i class="fas fa-user-plus"></i>
                Daftar Sekarang
            </a>
            <a href="{{ route('calculator') }}" class="btn-cta btn-outline-light">
                <i class="fas fa-calculator"></i>
                Hitung Emisi Anda
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/tentang.js') }}"></script>
@endpush