<!-- Navbar Component -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-5">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="CAMAR Logo" class="logo-img">
            <span class="brand-text">CAMAR</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                @if(!Auth::check() || Auth::user()->role === 'buyer')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('kalkulator') ? 'active' : '' }}" href="{{ route('calculator') }}">Kalkulator</a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('proyek') ? 'active' : '' }}" href="{{ route('projects.index') }}">Proyek</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('edukasi') ? 'active' : '' }}" href="{{ route('edukasi') }}">Edukasi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('tentang') ? 'active' : '' }}" href="{{ route('about') }}">Tentang Kami</a>
                </li>
            </ul>
            


            <div class="d-flex align-items-center">
                @auth
                    {{-- KONDISI SUDAH LOGIN: Tampilkan Profil & Cart di Semua Halaman --}}
                    @if(Auth::user()->role === 'buyer')
                    <div class="me-3 position-relative">
                        <a href="{{ route('cart.index') }}" class="nav-icon-link">
                            <i style="cursor: pointer; font-size: 1.2rem;" 
                            onclick="window.location.href='/cart'" 
                            class="fa-solid fa-cart-shopping text-success">
                            </i>
                            <span id="cartBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" 
                                style="
                                        display: flex;
                                        position: absolute;
                                        top: -8px; right: -8px;
                                        background: #e74c3c;
                                        color: white;
                                        border-radius: 50%;
                                        width: 18px; height: 18px;
                                        font-size: 0.6rem;
                                        font-weight: 700;
                                        line-height: 18px;
                                        text-align: center;
                                        justify-content: center;
                                        align-items: center;
                                ">
                                {{ $cartCount }}
                            </span>
                            <!-- <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" 
                            style="
                                    display: flex;
                                    position: absolute;
                                    top: -8px; right: -8px;
                                    background: #e74c3c;
                                    color: white;
                                    border-radius: 50%;
                                    width: 18px; height: 18px;
                                    font-size: 0.6rem;;
                                    font-weight: 700;
                                    line-height: 18px;
                                    text-align: center;
                                ">
                                {{-- Menghitung jumlah item unik di dalam session 'cart' --}}
                                {{ count(session('cart', [])) }}
                            </span> -->
                        </a>
                    </div>
                    @endif

                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center p-0" href="#" id="profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->profile_photo_url }}" 
                                alt="Profile" 
                                class="rounded-circle border border-2 border-success" 
                                style="width: 35px; height: 35px; object-fit: cover;">
                            <span class="ms-2 d-none d-md-inline fw-medium text-dark">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="profileDrop">
                            <li>
                                <a class="dropdown-item py-2" href="{{
                                    Auth::user()->isAdministrator()
                                        ? route('admin.dashboard')
                                        : (Auth::user()->isSeller() ? route('seller.dashboard') : route('dashboard'))
                                }}">
                                    <i class="fas fa-tachometer-alt me-2 text-success"></i> Dashboard
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                            </li>
                        </ul>
                    </div>

                @else
                    {{-- KONDISI BELUM LOGIN: Hanya tampilkan tombol Login --}}
                    <a href="{{ route('login') }}" class="btn btn-login px-4">Login</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
