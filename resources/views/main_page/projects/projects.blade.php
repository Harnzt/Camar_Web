@extends('main_page.layout.app')

@section('title', 'Proyek Carbon Offset')
@section('description', 'Proyek Carbon Offset - Pilih proyek verified carbon offset untuk mengurangi jejak karbon perusahaan Anda')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/projects.css') }}">
@endpush


@push('scripts')
<script>
    const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    const userRole   = "{{ Auth::check() ? Auth::user()->role : 'guest' }}";
    
    const userEmission = {
        total_kg: {{ $emission->total_kg ?? 0 }},
        tree_equivalent: {{ $emission ? ($emission->total_kg / 22) : 0 }} 
    };
</script>
<script src="{{ asset('js/projects.js') }}"></script>
@endpush

@section('content')
    {{-- SECTION: HERO --}}
    <section class="projects-hero" id="projects">
        <div class="projects-hero-bg">
            <img src="{{ asset('images/pabrik0.png') }}" alt="Hero Background">
        </div>
        <div class="container">
            <div class="projects-hero-content">
                <h1 class="projects-hero-title">Proyek Carbon Offset</h1>
                <p class="projects-hero-description">
                    Pilih proyek verified carbon offset untuk mengurangi jejak karbon perusahaan Anda.
                    Dukung proyek reforestasi, energi bersih, dan konservasi di seluruh Indonesia.
                </p>

                @auth
                    @if(Auth::user()->isSeller())
                        <div class="hero-seller-actions">
                            <a href="{{ route('seller.projects.create') }}" class="btn-add-project">
                                + Tambah Proyek Baru
                            </a>
                        </div>
                    @endif
                @endauth

            </div>
        </div>
    </section>

    <main class="projects-page">
        <div class="container">

        @auth
            @if(Auth::user()->isBuyer())
            {{-- SECTION: EMISSION STATS (Khusus Buyer) --}}
            <section class="emission-stats">
                <div class="stats-card">
                    <div class="stats-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M9,17H7v-7h2V17z M13,17h-2V7h2V17z M17,17h-2v-4h2V17z"/>
                        </svg>
                    </div>
                    <div class="stats-content">
                        <div class="stats-label">Total Emisi Anda</div>
                        <div class="stats-value" id="total-emissions">0</div>
                        <div class="stats-unit">kg CO₂</div>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="stats-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2C8.13,2,5,5.13,5,9c0,5.25,7,13,7,13s7-7.75,7-13C19,5.13,15.87,2,12,2z M12,11.5c-1.38,0-2.5-1.12-2.5-2.5 s1.12-2.5,2.5-2.5s2.5,1.12,2.5,2.5S13.38,11.5,12,11.5z"/>
                        </svg>
                    </div>
                    <div class="stats-content">
                        <div class="stats-label">Ekuivalen</div>
                        <div class="stats-value" id="tree-equivalent">0</div>
                        <div class="stats-unit">Pohon</div>
                    </div>
                </div>

                <div class="stats-card stats-card-highlight">
                    <div class="stats-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17,8C8,10,5.9,16.17,3.82,21.34L5.71,22L6.66,19.7C7.14,19.87,7.64,20,8,20C19,20,22,3,22,3C21,5,14,5.25,9,6.25C4,7.25,2,11.5,2,13.5C2,15.5,3.75,17.25,3.75,17.25C7,8,17,8,17,8Z"/>
                        </svg>
                    </div>
                    <div class="stats-content">
                        <div class="stats-label">Offset Dibutuhkan</div>
                        <div class="stats-value" id="offset-needed">0</div>
                        <div class="stats-unit">ton CO₂</div>
                    </div>
                </div>
            </section>
            @endif
        @endauth
        
        {{-- SECTION: SEARCH & SORT --}}
        <section class="search-sort-section">
            <div class="search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5,14H14.71L14.43,13.73C15.41,12.59,16,11.11,16,9.5C16,5.91,13.09,3,9.5,3S3,5.91,3,9.5S5.91,16,9.5,16 C11.11,16,12.59,15.41,13.73,14.43L14,14.71V15.5L19,20.49L20.49,19L15.5,14z M9.5,14C7.01,14,5,11.99,5,9.5S7.01,5,9.5,5 S14,7.01,14,9.5S11.99,14,9.5,14z"/>
                </svg>
                <input type="text" class="search-input" id="search-input" placeholder="Cari proyek berdasarkan nama, lokasi, atau kategori...">
            </div>
            <div class="sort-box">
                <select class="sort-select" id="sort-select">
                    <option value="relevant">Paling Relevan</option>
                    <option value="price-low">Harga Terendah</option>
                    <option value="price-high">Harga Tertinggi</option>
                    <option value="newest">Terbaru</option>
                    <option value="capacity">Kapasitas Terbesar</option>
                </select>
            </div>
        </section>
        
        @auth
            {{-- BUYER ONLY: Section Rekomendasi Proyek (Smart Matching) --}}
            @if(Auth::user()->isBuyer())
                @if(isset($recommendedProjects) && $recommendedProjects->isNotEmpty())
                    <section class="recommended-section mb-5">
                        <div class="recommended-header">
                            <h2 class="recommended-title">Rekomendasi Proyek untuk Anda</h2>
                            <p class="recommended-subtitle">
                                @if($emission)
                                    Proyek yang paling sesuai dengan profil emisi dan kebutuhan offset Anda.
                                @elseif($user?->isCompany())
                                    Pilihan awal berdasarkan jenis industri perusahaan Anda. Hitung emisi untuk memperoleh skor kecocokan yang lebih akurat.
                                @else
                                    Pilihan proyek untuk memulai. Hitung emisi untuk memperoleh rekomendasi yang lebih akurat.
                                @endif
                            </p>
                        </div>

                        <div class="recommended-grid row g-4">
                            @foreach($recommendedProjects as $project)
                                <div class="col-md-6 col-lg-4">

                                    <div class="project-card">

                                        <span class="project-card__badge {{ !$emission ? 'project-card__badge--initial' : '' }}">
                                            @if($emission)
                                                Cocok {{ number_format($project->recommendation_score, 0) }}%
                                            @else
                                                {{ $project->recommendation_label }}
                                            @endif
                                        </span>
                                        
                                        <div class="project-image project-card__image">
                                            <img src="{{ $project->image ? asset('images/' . $project->image) : asset('images/placeholder-project.jpg') }}" alt="{{ $project->name }}">
                                        </div>
                                        
                                        <div class="project-card__body">
                                            <div class="project-card__category">{{ $project->category }}</div>
                                            
                                            <p class="project-card__company">{{ $project->company_name }}</p>
                                            
                                            <h3 class="project-card__title" line-clamp-2>{{ $project->name }}</h3>
                                            
                                            <div class="project-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $project->location ?? '-' }}
                                            </div>
                                                                            
                                            <p class="project-card__description">{{ Str::limit($project->description, 100) }}</p>

                                            @if($emission)
                                                <div class="recommendation-summary">
                                                    <div class="recommendation-score-row">
                                                        <span>Skor kecocokan</span>
                                                        <strong>{{ number_format($project->recommendation_score, 1) }}/100</strong>
                                                    </div>
                                                    <div class="recommendation-score-track">
                                                        <div class="recommendation-score-fill"
                                                            style="width: {{ min(100, $project->recommendation_score) }}%">
                                                        </div>
                                                    </div>

                                                    <div class="recommendation-breakdown">
                                                        <span>Emisi {{ $project->recommendation_breakdown['emission_match'] }}/35</span>
                                                        <span>Anggaran {{ $project->recommendation_breakdown['budget_match'] }}/25</span>
                                                        <span>Stok {{ $project->recommendation_breakdown['stock_coverage'] }}/20</span>
                                                        <span>Lokasi {{ $project->recommendation_breakdown['location_match'] }}/10</span>
                                                        <span>Standar {{ $project->recommendation_breakdown['verification'] }}/10</span>
                                                    </div>

                                                    <ul class="recommendation-reasons">
                                                        @foreach($project->recommendation_reasons as $reason)
                                                            <li>{{ $reason }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            
                                            <div class="project-card__meta">
                                                <div class="project-duration-wrapper">
                                                    <span class="duration-icon">📅</span>
                                                    <span class="project-duration">{{ $project->duration_months }} Bulan</span>
                                                </div>
                                                <span class="project-card__capacity">Stok: {{ number_format($project->stock_available) }} ton</span>
                                            </div>
                        
                                            <div class="project-card__meta">
                                                <span class="project-card__price">Rp {{ number_format($project->price_per_ton, 0, ',', '.') }} / ton</span>
                                            </div>
                                        </div>

                                        <div class="project-card__footer">
                                            <a href="{{ route('projects.show', $project->id) }}" class="btn-action btn-action--primary">Pilih Paket</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif

            {{-- SELLER ONLY: BAGIAN 1 - PROYEK SAYA SENDIRI --}}
            @if(Auth::user()->isSeller())
                <section class="seller-own-projects-section mb-5">
                    <div class="recommended-header">
                        <h2 class="recommended-title" style="color: #28a745;"><i class="fas fa-folder-open"></i> Portofolio Proyek Saya</h2>
                        <p class="recommended-subtitle">Daftar seluruh proyek mitigasi karbon yang Anda terbitkan dan kelola di pasar CAMAR.</p>
                    </div>
                    
                    <div class="row g-4">
                        @php $hasOwnProject = false; @endphp
                        @foreach($projects as $project)
                            @if($project->seller_id === Auth::user()->id)
                                @php $hasOwnProject = true; @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="project-card" style="border: 2px solid #28a745;">
                                        <div class="project-image project-card__image">
                                            <img src="{{ $project->image ? asset('images/' . $project->image) : asset('images/placeholder-project.jpg') }}" alt="{{ $project->name }}">
                                        </div>
                                        <div class="project-card__body">
                                            <div class="project-card__category">{{ $project->category }}</div>
                                            <h3 class="project-card__title">{{ $project->name }}</h3>
                                            <p class="project-card__company">{{ $project->company_name }}</p>
                                            <p class="project-card__description">{{ Str::limit($project->description, 100) }}</p>
                                            <div class="project-duration-wrapper">
                                                <span class="duration-icon">📅</span>
                                                <span class="project-duration">{{ $project->duration_months }} Bulan</span>
                                            </div>
                                            <div class="project-card__meta">
                                                <span class="project-card__price">Rp {{ number_format($project->price_per_ton, 0, ',', '.') }} / ton</span>
                                                <span class="project-card__capacity">Stok: {{ number_format($project->stock_available) }} ton</span>
                                            </div>
                                        </div>
                                        <div class="project-card__footer">
                                            <a href="{{ route('seller.dashboard') }}#kelola-{{ $project->id }}" class="btn-action btn-action--manage">
                                                Kelola Proyek
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if(!$hasOwnProject)
                            <div class="col-12 text-center py-4 text-muted">
                                <p>Anda belum menerbitkan proyek apa pun. Klik "+ Tambah Proyek Baru" untuk meluncurkan proyek Anda.</p>
                            </div>
                        @endif
                    </div>
                </section>
                <hr style="margin: 3rem 0; border-color: #ddd;">
            @endif
        @endauth


        {{-- ===================================================== --}}
        {{-- SECTION: KATALOG PROYEK UTAMA                      --}}
        {{-- (Untuk Guest & Buyer: Melihat Semua. Untuk Seller: Melihat Milik Orang Lain) --}}
        {{-- ===================================================== --}}
        <section class="projects-grid-section all-projects-section">
            <div class="recommended-header">
                <h2 class="recommended-title">
                    @auth
                        @if(Auth::user()->isSeller())
                            <i class="fas fa-chart-line"></i> Eksplorasi Proyek Pasar 
                        @else
                            Semua Proyek Carbon Offset
                        @endif
                    @else
                        Semua Proyek Carbon Offset
                    @endauth
                </h2>
                <p class="recommended-subtitle">
                    @auth
                        @if(Auth::user()->isSeller())
                            Pantau pergerakan harga, stok ketersediaan, dan kategori proyek carbon milik seller lain di seluruh Indonesia.
                        @else
                            Jelajahi seluruh proyek verified carbon offset aktif untuk menyeimbangkan emisi karbon Anda.
                        @endif
                    @else
                        Jelajahi seluruh proyek verified carbon offset aktif untuk menyeimbangkan emisi karbon Anda.
                    @endauth
                </p>
            </div>

            <div class="projects-grid row g-4" id="projects-grid">
                @php $hasMarketProject = false; @endphp
                @forelse($projects as $project)
                    {{-- JIKA USER ADALAH SELLER, JANGAN TAMPILKAN PROYEK MILIKNYA SENDIRI DI SINI --}}
                    @auth
                        @if(Auth::user()->isSeller() && $project->seller_id === Auth::user()->id)
                            @continue
                        @endif
                    @endauth

                    @php $hasMarketProject = true; @endphp
                    <div class="col-md-6 col-lg-4" data-project-id="{{ $project->id }}">
                        <div class="project-card">
                            
                            <div class="project-card__image">
                                <img src="{{ $project->image ? asset('images/' . $project->image) : asset('images/placeholder-project.jpg') }}" alt="{{ $project->name }}">
                            </div>

                            <div class="project-card__body">
                                <div class="project-card__category">{{ $project->category }}</div>
                                
                                <div class="project-card__company">{{ $project->company_name ?? 'Seller' }}</div>
                                
                                <h3 class="project-card__title" line-clamp-2>{{ $project->name }}</h3>
                                <!-- <p class="project-company">{{ $project->company_name }}</p> -->
                                
                                <div class="project-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $project->location ?? '-' }}
                                </div>
                                
                                <p class="project-card__description">{{ Str::limit($project->description, 100) }}</p>
                                
                                <div class="project-card__meta">   
                                    <div class="project-duration-wrapper">
                                        <span class="duration-icon">📅 </span>
                                        <span class="project-duration">{{ $project->duration_months }} Bulan</span>
                                    </div>
                                    <span class="project-card__capacity">Stok: {{ number_format($project->stock_available) }} ton</span>
                                </div>

                                <div class="project-card__meta">
                                    <span class="project-card__price">Rp {{ number_format($project->price_per_ton, 0, ',', '.') }} / ton</span>
                                </div>
                            </div>

                            <div class="project-card__footer">
                                @guest
                                    <a href="{{ route('login') }}" class="btn-action btn-action--guest">Detail</a>
                                @endguest

                                @auth
                                    @if(Auth::user()->isBuyer())
                                        <a href="{{ route('projects.show', $project->id) }}" class="btn-action btn-action--primary">Pilih Paket</a>
                                    @elseif(Auth::user()->isSeller())
                                        <a href="{{ route('projects.show', $project->id) }}" class="btn-action btn-action--view">Lihat Detail Proyek</a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                @empty
                    @if(!$hasMarketProject)
                        <div class="col-12 text-center py-5">
                            <div class="projects-empty"><p>Belum ada proyek lain yang tersedia saat ini.</p></div>
                        </div>
                    @endif
                @endforelse

                {{-- Fallback teks jika semua proyek adalah milik seller itu sendiri sehingga list bawah kosong --}}
                @if(!$hasMarketProject)
                    <div class="col-12 text-center py-5">
                        <div class="projects-empty"><p>Belum ada proyek milik penjual lain di pasar.</p></div>
                    </div>
                @endif

            </div>
        </section>

        {{-- SECTION: PAGINATION --}}
        <section class="pagination-section">
            {{ $projects->links() }}
        </section>

        </div>
    </main>
@endsection

@push('scripts')
<script>
    const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    const userRole   = "{{ Auth::check() ? Auth::user()->role : 'guest' }}";
</script>
<script src="{{ asset('js/projects.js') }}"></script>
@endpush
