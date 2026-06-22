@extends('main_page.layout.app')

@section('title', 'Edukasi Carbon Offset')
@section('description', 'Edukasi Carbon Offset - Pelajari tentang carbon offset, kredit karbon, dan strategi Net-Zero')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/edukasi.css') }}">
@endpush

@section('content')
</head>
<body>
    <!-- Include Navbar -->

    <!-- Section 1: Hero -->
    <section class="edukasi-hero">
        <div class="edukasi-hero-bg">
            <img src="{{ asset('images/forest0.png') }}" alt="Climate Education">
        </div>
        <div class="container">
            <div class="edukasi-hero-content">
                <h1 class="edukasi-hero-title">Memahami Perubahan Iklim</h1>
                <p class="edukasi-hero-description">
                    Pelajari perjalanan emisi karbon, dampaknya terhadap bumi, dan bagaimana kita dapat berkontribusi untuk masa depan yang berkelanjutan
                </p>
            </div>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider-90"></div>

    <!-- Section 2: Roadmap Perjalanan Emisi Karbon -->
    <section class="roadmap-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Perjalanan Emisi Karbon</h2>
                <p class="section-subtitle">Dari sumber emisi hingga solusi penanggulangan perubahan iklim</p>
            </div>

            <div class="roadmap-timeline">
                <!-- Step 1: Sumber Emisi -->
                <div class="roadmap-step">
                    <div class="step-number">01</div>
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-industry"></i>
                        </div>
                        <h3 class="step-title">Sumber Emisi</h3>
                        <p class="step-description">
                            Emisi gas rumah kaca global mencapai <strong>59 GtCO₂-eq</strong> pada tahun 2019. Sektor energi, industri, transportasi, dan bangunan menyumbang <strong>79%</strong>, sedangkan pertanian dan kehutanan menyumbang <strong>22%</strong>.
                        </p>
                        <div class="step-stats">
                            <div class="stat-bar">
                                <span class="stat-label">Energi & Industri</span>
                                <div class="stat-progress">
                                    <div class="stat-fill" style="width: 79%"></div>
                                </div>
                                <span class="stat-value">79%</span>
                            </div>
                            <div class="stat-bar">
                                <span class="stat-label">AFOLU</span>
                                <div class="stat-progress">
                                    <div class="stat-fill" style="width: 22%"></div>
                                </div>
                                <span class="stat-value">22%</span>
                            </div>
                        </div>
                    </div>
                    <div class="step-connector"></div>
                </div>

                <!-- Step 2: Akumulasi -->
                <div class="roadmap-step">
                    <div class="step-number">02</div>
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <h3 class="step-title">Akumulasi di Atmosfer</h3>
                        <p class="step-description">
                            GRK terakumulasi di atmosfer melebihi kapasitas alami bumi. Konsentrasi CO₂ mencapai 410 ppm (tertinggi dalam 2 juta tahun), CH₄ 1866 ppb, dan N₂O 332 ppb.
                        </p>
                        <div class="gas-levels">
                            <div class="gas-item">
                                <span class="gas-name">CO₂</span>
                                <span class="gas-value">410 ppm</span>
                                <span class="gas-note">Tertinggi 2 juta tahun</span>
                            </div>
                            <div class="gas-item">
                                <span class="gas-name">CH₄</span>
                                <span class="gas-value">1866 ppb</span>
                                <span class="gas-note">Tertinggi 800 ribu tahun</span>
                            </div>
                        </div>
                    </div>
                    <div class="step-connector"></div>
                </div>

                <!-- Step 3: Pemanasan Global -->
                <div class="roadmap-step">
                    <div class="step-number">03</div>
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-temperature-high"></i>
                        </div>
                        <h3 class="step-title">Pemanasan Global</h3>
                        <p class="step-description">
                            Konsentrasi GRK menyebabkan peningkatan suhu global +1.09°C dibanding periode pra-industri. Daratan mengalami kenaikan lebih tinggi (+1.59°C) dibanding lautan (+0.88°C).
                        </p>
                        <div class="temp-grid">
                            <div class="temp-box">
                                <div class="temp-value">+1.09°C</div>
                                <div class="temp-label">Global</div>
                            </div>
                            <div class="temp-box">
                                <div class="temp-value">+1.59°C</div>
                                <div class="temp-label">Daratan</div>
                            </div>
                            <div class="temp-box">
                                <div class="temp-value">+0.88°C</div>
                                <div class="temp-label">Lautan</div>
                            </div>
                        </div>
                    </div>
                    <div class="step-connector"></div>
                </div>

                <!-- Step 4: Dampak -->
                <div class="roadmap-step">
                    <div class="step-number">04</div>
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="step-title">Dampak Perubahan Iklim</h3>
                        <p class="step-description">
                            3.3-3.6 miliar orang hidup dalam konteks yang sangat rentan. Kenaikan muka laut 3.7 mm/tahun, cuaca ekstrem meningkat, ratusan spesies punah lokal.
                        </p>
                        <div class="impact-tags">
                            <span class="impact-tag"><i class="fas fa-water"></i> Kenaikan Laut</span>
                            <span class="impact-tag"><i class="fas fa-bolt"></i> Cuaca Ekstrem</span>
                            <span class="impact-tag"><i class="fas fa-seedling"></i> Biodiversitas</span>
                        </div>
                    </div>
                    <div class="step-connector"></div>
                </div>

                <!-- Step 5: Mitigasi -->
                <div class="roadmap-step">
                    <div class="step-number">05</div>
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3 class="step-title">Mitigasi & Adaptasi</h3>
                        <p class="step-description">
                            Untuk membatasi pemanasan 1.5°C, diperlukan pengurangan emisi 43% pada 2030. Net zero CO₂ harus tercapai awal 2050an.
                        </p>
                        <div class="action-box">
                            <div class="action-item">
                                <i class="fas fa-chart-line-down"></i>
                                <span>Pengurangan 43% emisi (2030)</span>
                            </div>
                            <div class="action-item">
                                <i class="fas fa-solar-panel"></i>
                                <span>Transisi energi terbarukan</span>
                            </div>
                        </div>
                    </div>
                    <div class="step-connector"></div>
                </div>

                <!-- Step 6: Carbon Offset -->
                <div class="roadmap-step">
                    <div class="step-number">06</div>
                    <div class="step-card highlight">
                        <div class="step-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h3 class="step-title">Carbon Offset</h3>
                        <p class="step-description">
                            Mengimbangi emisi yang tidak dapat dihindari dengan proyek pengurangan emisi. Verified Carbon Credits, reforestasi, energi terbarukan mendukung Net Zero 2060 Indonesia.
                        </p>
                        <div class="offset-tags">
                            <span class="offset-tag">VCC</span>
                            <span class="offset-tag">Reforestasi</span>
                            <span class="offset-tag">Net Zero 2060</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider-90"></div>

    <!-- Section 3: Dampak Emisi Karbon -->
    <section class="impact-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Dampak Perubahan Iklim</h2>
                <p class="section-subtitle">Ancaman nyata yang mempengaruhi kehidupan kita</p>
            </div>

            <div class="impact-grid">
                <!-- Impact 1 -->
                <div class="impact-module">
                    <div class="impact-icon-wrapper">
                        <i class="fas fa-temperature-high"></i>
                    </div>
                    <div class="impact-content">
                        <h3 class="impact-title">Kenaikan Suhu Global</h3>
                        <div class="impact-value">+1.1°C</div>
                        <p class="impact-text">
                            Suhu global meningkat 1.1°C dibanding periode pra-industri, dengan daratan mengalami kenaikan lebih tinggi (+1.59°C)
                        </p>
                    </div>
                </div>

                <!-- Impact 2 -->
                <div class="impact-module">
                    <div class="impact-icon-wrapper">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="impact-content">
                        <h3 class="impact-title">Cuaca Ekstrem</h3>
                        <div class="impact-value">Meningkat</div>
                        <p class="impact-text">
                            Frekuensi dan intensitas gelombang panas, hujan lebat, kekeringan, dan siklon tropis meningkat signifikan
                        </p>
                    </div>
                </div>

                <!-- Impact 3 -->
                <div class="impact-module">
                    <div class="impact-icon-wrapper">
                        <i class="fas fa-water"></i>
                    </div>
                    <div class="impact-content">
                        <h3 class="impact-title">Kenaikan Muka Laut</h3>
                        <div class="impact-value">3.7 mm/tahun</div>
                        <p class="impact-text">
                            Permukaan laut naik rata-rata 3.7 mm per tahun (2006-2018), mengancam wilayah pesisir dan pulau-pulau kecil
                        </p>
                    </div>
                </div>

                <!-- Impact 4 -->
                <div class="impact-module">
                    <div class="impact-icon-wrapper">
                        <i class="fas fa-dove"></i>
                    </div>
                    <div class="impact-content">
                        <h3 class="impact-title">Kehilangan Biodiversitas</h3>
                        <div class="impact-value">Ratusan Spesies</div>
                        <p class="impact-text">
                            Ratusan kepunahan lokal spesies akibat panas ekstrem, dengan peristiwa kematian massal di darat dan laut
                        </p>
                    </div>
                </div>

                <!-- Impact 5 -->
                <div class="impact-module">
                    <div class="impact-icon-wrapper">
                        <i class="fas fa-bread-slice"></i>
                    </div>
                    <div class="impact-content">
                        <h3 class="impact-title">Krisis Pangan & Air</h3>
                        <div class="impact-value">50% Penduduk</div>
                        <p class="impact-text">
                            Sekitar setengah populasi dunia mengalami kelangkaan air parah setidaknya sebagian tahun
                        </p>
                    </div>
                </div>

                <!-- Impact 6 -->
                <div class="impact-module">
                    <div class="impact-icon-wrapper">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="impact-content">
                        <h3 class="impact-title">Risiko Kesehatan</h3>
                        <div class="impact-value">15x Lebih Tinggi</div>
                        <p class="impact-text">
                            Mortalitas akibat banjir, kekeringan, dan badai 15x lebih tinggi di wilayah sangat rentan
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider-90"></div>

    <!-- Section 4: Carbon Offset -->
    <section class="offset-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Carbon Offset</h2>
                <p class="section-subtitle">Solusi untuk mencapai Net Zero Emissions</p>
            </div>

            <!-- Apa itu Carbon Offset -->
            <div class="offset-module">
                <div class="module-header">
                    <div class="module-number">01</div>
                    <h3 class="module-title">Apa itu Carbon Offset?</h3>
                </div>
                <div class="module-body">
                    <p class="module-text">
                        <strong>Carbon Offset</strong> adalah mekanisme pengurangan emisi Gas Rumah Kaca (GRK) untuk mengkompensasi emisi yang dihasilkan di tempat lain. 
                        Setiap kredit karbon setara dengan pengurangan atau penyerapan <span class="highlight">1 ton CO₂ ekuivalen (tCO₂e)</span> dari atmosfer.
                    </p>
                    <div class="credit-box">
                        <div class="credit-visual">
                            <i class="fas fa-certificate"></i>
                            <span class="credit-equation">1 Kredit Karbon = 1 tCO₂e</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cara Kerja -->
            <div class="offset-module">
                <div class="module-header">
                    <div class="module-number">02</div>
                    <h3 class="module-title">Cara Kerja Carbon Offset</h3>
                </div>
                <div class="module-body">
                    <div class="workflow-grid">
                        <div class="workflow-item">
                            <div class="workflow-number">1</div>
                            <div class="workflow-icon"><i class="fas fa-calculator"></i></div>
                            <h4>Hitung Emisi</h4>
                            <p>Identifikasi dan hitung total emisi karbon dari aktivitas bisnis atau pribadi Anda</p>
                        </div>
                        <div class="workflow-arrow"><i class="fas fa-arrow-right"></i></div>
                        <div class="workflow-item">
                            <div class="workflow-number">2</div>
                            <div class="workflow-icon"><i class="fas fa-chart-line-down"></i></div>
                            <h4>Kurangi Emisi</h4>
                            <p>Implementasikan strategi pengurangan emisi melalui efisiensi energi</p>
                        </div>
                        <div class="workflow-arrow"><i class="fas fa-arrow-right"></i></div>
                        <div class="workflow-item">
                            <div class="workflow-number">3</div>
                            <div class="workflow-icon"><i class="fas fa-handshake"></i></div>
                            <h4>Offset Sisa Emisi</h4>
                            <p>Beli kredit karbon terverifikasi untuk mengimbangi emisi yang tidak dapat dihindari</p>
                        </div>
                        <div class="workflow-arrow"><i class="fas fa-arrow-right"></i></div>
                        <div class="workflow-item highlight">
                            <div class="workflow-number">4</div>
                            <div class="workflow-icon"><i class="fas fa-check-circle"></i></div>
                            <h4>Capai Net Zero</h4>
                            <p>Mencapai keseimbangan nol emisi bersih melalui kombinasi pengurangan dan offset</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kegunaan & Manfaat -->
            <div class="offset-module">
                <div class="module-header">
                    <div class="module-number">03</div>
                    <h3 class="module-title">Kegunaan & Manfaat</h3>
                </div>
                <div class="module-body">
                    <div class="benefits-grid">
                        <div class="benefit-card">
                            <div class="benefit-icon"><i class="fas fa-bullseye"></i></div>
                            <h4>Mencapai Target Net Zero</h4>
                            <p>Membantu organisasi mencapai target netralitas karbon sesuai komitmen iklim global</p>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-icon"><i class="fas fa-globe-americas"></i></div>
                            <h4>Kontribusi Aksi Global</h4>
                            <p>Berkontribusi nyata dalam upaya global membatasi pemanasan di bawah 1.5°C</p>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-icon"><i class="fas fa-award"></i></div>
                            <h4>Meningkatkan Reputasi</h4>
                            <p>Menunjukkan komitmen terhadap keberlanjutan dan tanggung jawab lingkungan</p>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-icon"><i class="fas fa-balance-scale"></i></div>
                            <h4>Kepatuhan Regulasi</h4>
                            <p>Memenuhi persyaratan regulasi emisi dan standar keberlanjutan</p>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-icon"><i class="fas fa-leaf"></i></div>
                            <h4>Perlindungan Lingkungan</h4>
                            <p>Mendukung proyek yang melindungi hutan, biodiversitas, dan ekosistem</p>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-icon"><i class="fas fa-users"></i></div>
                            <h4>Manfaat Sosial</h4>
                            <p>Memberikan manfaat sosial-ekonomi untuk masyarakat lokal</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kebijakan Indonesia -->
            <div class="offset-module">
                <div class="module-header">
                    <div class="module-number">04</div>
                    <h3 class="module-title">Kebijakan Carbon Offset di Indonesia</h3>
                </div>
                <div class="module-body">
                    <div class="timeline-wrapper">
                        <div class="timeline-line"></div>
                        <div class="timeline-items">
                            <div class="timeline-item">
                                <div class="timeline-year">2016</div>
                                <div class="timeline-content">
                                    <h4>Ratifikasi Paris Agreement</h4>
                                    <p>Indonesia meratifikasi Persetujuan Paris dengan komitmen menurunkan emisi GRK 29% dengan upaya sendiri dan hingga 41% dengan dukungan internasional pada 2030</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-year">2021</div>
                                <div class="timeline-content">
                                    <h4>Enhanced NDC</h4>
                                    <p>Peningkatan ambisi NDC dengan target pengurangan emisi yang lebih ambisius di sektor energi, kehutanan, pertanian, dan limbah</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-year">2022</div>
                                <div class="timeline-content">
                                    <h4>Pajak Karbon</h4>
                                    <p>Penerapan pajak karbon sebagai instrumen ekonomi untuk mendorong pengurangan emisi dan transisi ke ekonomi rendah karbon</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-year">2023</div>
                                <div class="timeline-content">
                                    <h4>Bursa Karbon IDXCarbon</h4>
                                    <p>Peluncuran perdagangan karbon di Bursa Efek Indonesia untuk memfasilitasi transaksi kredit karbon</p>
                                </div>
                            </div>
                            <div class="timeline-item highlight">
                                <div class="timeline-year">2060</div>
                                <div class="timeline-content">
                                    <h4>Target Net Zero Emission</h4>
                                    <p>Indonesia berkomitmen mencapai Net Zero Emission (NZE) atau netralitas karbon pada tahun 2060</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="offset-cta">
                <h3>Siap Mengambil Aksi untuk Iklim?</h3>
                <p>Mulai perjalanan menuju net zero dengan menghitung jejak karbon Anda dan mendukung proyek berkelanjutan</p>
                <div class="cta-buttons">
                    <a href="{{ route('calculator') }}" class="btn btn-primary">
                        <i class="fas fa-calculator"></i>
                        Hitung Jejak Karbon
                    </a>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline">
                        <i class="fas fa-seedling"></i>
                        Lihat Proyek Kami
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/edukasi.js') }}"></script>
@endsection

@push('scripts')
<script src="{{ asset('js/edukasi.js') }}"></script>
@endpush