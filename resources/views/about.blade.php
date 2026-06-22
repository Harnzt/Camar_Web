@extends('layouts.app')

@section('title', 'Tentang - CAMAR')

@section('content')
<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, #67C090 0%, #2D9F6E 100%); padding: 150px 0 100px; color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Tentang CAMAR</h1>
                <p class="lead mb-4">
                    Carbon Offset Market and Registry (CAMAR) adalah platform yang berkomitmen untuk membantu 
                    individu dan organisasi mencapai netralitas karbon melalui proyek-proyek berkelanjutan.
                </p>
            </div>
            <div class="col-lg-6">
                <img src="{{ asset('resources/images/about-illustration.svg') }}" alt="About CAMAR" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper" style="background: #67C090; width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h3 class="ms-3 mb-0">Misi Kami</h3>
                        </div>
                        <p class="text-muted">
                            Menyediakan solusi carbon offset yang transparan, terverifikasi, dan berdampak nyata 
                            untuk membantu Indonesia mencapai target Net Zero Emission 2060.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper" style="background: #2D9F6E; width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3 class="ms-3 mb-0">Visi Kami</h3>
                        </div>
                        <p class="text-muted">
                            Menjadi platform carbon offset terdepan di Indonesia yang menghubungkan pelaku usaha 
                            dengan proyek-proyek berkelanjutan berkualitas tinggi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Nilai-Nilai Kami</h2>
            <p class="text-muted">Prinsip yang memandu setiap langkah kami</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: #67C090; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Transparansi</h4>
                    <p class="text-muted">Semua transaksi dan proyek dapat dilacak dan diverifikasi</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: #2D9F6E; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h4>Kualitas</h4>
                    <p class="text-muted">Hanya proyek terverifikasi dengan standar internasional</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="icon-wrapper mx-auto mb-3" style="background: #124170; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px;">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Kolaborasi</h4>
                    <p class="text-muted">Bekerja sama dengan berbagai pihak untuk dampak maksimal</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="fw-bold mb-4">Siap Bergabung dengan Aksi Iklim?</h2>
                <p class="lead text-muted mb-4">
                    Mulai perjalanan menuju net zero bersama CAMAR
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('calculator') }}" class="btn btn-lg" style="background: #67C090; color: white;">
                        <i class="fas fa-calculator me-2"></i>
                        Hitung Jejak Karbon
                    </a>
                    <a href="{{ route('projects') }}" class="btn btn-lg btn-outline-secondary">
                        <i class="fas fa-seedling me-2"></i>
                        Lihat Proyek
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection